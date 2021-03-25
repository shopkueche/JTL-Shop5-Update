<?php declare(strict_types=1);

namespace JTL\Installation;

use Exception;
use JTL\DB\NiceDB;
use JTL\DB\ReturnType;
use JTL\Exceptions\InvalidEntityNameException;
use jtl\Wizard\Question;
use jtl\Wizard\ShopWizard;
use stdClass;
use Systemcheck\Environment;
use Systemcheck\Platform\Filesystem;

/**
 * Class VueInstaller
 * @package JTL\Installation
 */
class VueInstaller
{
    /**
     * @var string
     */
    private $task;

    /**
     * @var array
     */
    private $post;

    /**
     * @var bool
     */
    private $cli;

    /**
     * @var NiceDB
     */
    private $db;

    /**
     * @var bool
     */
    private $responseStatus = true;

    /**
     * @var array
     */
    private $responseMessage = [];

    /**
     * @var array
     */
    private $payload = [];

    /**
     * Installer constructor.
     *
     * @param string     $task
     * @param array|null $post
     * @param bool       $cli
     */
    public function __construct(string $task, array $post = null, bool $cli = false)
    {
        $this->task = $task;
        $this->post = $post;
        $this->cli  = $cli;
    }

    /**
     *
     */
    public function run(): ?array
    {
        switch ($this->task) {
            case 'installedcheck':
                $this->getIsInstalled();
                break;
            case 'systemcheck':
                $this->getSystemCheck();
                break;
            case 'dircheck':
                $this->getDirectoryCheck();
                break;
            case 'credentialscheck':
                $this->getDBCredentialsCheck();
                break;
            case 'doinstall':
                $this->doInstall();
                break;
            case 'installdemodata':
                $this->installDemoData();
                break;
            case 'wizard':
                $this->executeWizard();
                break;
            default:
                break;
        }

        return $this->output();
    }

    /**
     * @return $this
     */
    private function executeWizard(): self
    {
        if ($this->initNiceDB($this->post['db'])) {
            $step   = (int)($this->post['stepId'] ?? 0);
            $wizard = new ShopWizard($step);
            if (isset($this->post['action']) && $this->post['action'] === 'setData') {
                foreach ($wizard->getQuestions() as $idx => $question) {
                    $questionId = $question->getID();
                    if ($question->getType() === Question::TYPE_BOOL) {
                        $wizard->answerQuestionByID(
                            $questionId,
                            isset($this->post['data'][$questionId]) && $this->post['data'][$questionId] === 'true'
                        );
                    } elseif (isset($this->post['data'][$questionId])) {
                        $wizard->answerQuestionByID($questionId, $this->post['data'][$questionId]);
                    }
                }
                $wizard->getStep()->finishStep(false);
            }
            if ($step > 0) {
                $this->payload['questions'] = $wizard->getFilteredQuestions();
            } else {
                $this->payload['isSynced'] = $wizard->getStep()->isSync();
                $this->payload['step']     = $wizard->getStep();
            }
        }
        $this->sendResponse();

        return $this;
    }

    /**
     *
     */
    private function output(): ?array
    {
        if (!$this->cli) {
            echo \json_encode($this->payload);
            exit(0);
        }

        return $this->payload;
    }

    /**
     *
     */
    private function sendResponse(): void
    {
        if ($this->responseStatus === true && empty($this->responseMessage)) {
            $this->responseMessage[] = 'executeSuccess';
        }
        echo \json_encode([
            'ok'      => $this->responseStatus,
            'payload' => $this->payload,
            'msg'     => \implode('<br>', $this->responseMessage)
        ]);
        exit(0);
    }

    /**
     * @param array $credentials
     * @return bool
     */
    private function initNiceDB(array $credentials): bool
    {
        if (!isset($credentials['host'], $credentials['user'], $credentials['pass'], $credentials['name'])) {
            return false;
        }
        try {
            if (!empty($credentials['socket'])) {
                \define('DB_SOCKET', $credentials['socket']);
            }
            \ifndef('DB_HOST', $credentials['host']);
            \ifndef('DB_USER', $credentials['user']);
            \ifndef('DB_PASS', $credentials['pass']);
            \ifndef('DB_NAME', $credentials['name']);
            $this->db = new NiceDB(
                $credentials['host'],
                $credentials['user'],
                $credentials['pass'],
                $credentials['name']
            );
        } catch (Exception $e) {
            $this->responseMessage[] = $e->getMessage();
            $this->responseStatus    = false;

            return false;
        }

        return true;
    }

    /**
     * @return VueInstaller
     * @throws InvalidEntityNameException
     */
    private function doInstall(): self
    {
        if ($this->initNiceDB($this->post['db'])) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=0', ReturnType::DEFAULT);
            $schema = \PFAD_ROOT . 'install/initial_schema.sql';
            if (!\file_exists($schema)) {
                $this->responseMessage[] = 'File does not exists: ' . $schema;
            }
            $this->parseMysqlDump($schema);
            $this->insertUsers();
            $blowfishKey = $this->getUID(30);
            $this->writeConfigFile($this->post['db'], $blowfishKey);
            $this->payload['secretKey'] = $blowfishKey;
            $this->db->query('SET FOREIGN_KEY_CHECKS=1', ReturnType::DEFAULT);
        }

        if ($this->cli) {
            $this->payload['error'] = !$this->responseStatus;
            $this->payload['msg']   = $this->responseStatus === true && empty($this->responseMessage)
                ? 'executeSuccess'
                : $this->responseMessage;
        } else {
            $this->sendResponse();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function installDemoData(): self
    {
        if ($this->initNiceDB($this->post['db'])) {
            $demoData = new DemoDataInstaller($this->db);
            $demoData->run();
            $this->responseStatus = true;
        }
        if ($this->cli) {
            $this->payload['error'] = !$this->responseStatus;
        } else {
            $this->sendResponse();
        }

        return $this;
    }

    /**
     * @param array  $credentials
     * @param string $blowfishKey
     * @return bool
     */
    private function writeConfigFile(array $credentials, string $blowfishKey): bool
    {
        if (!isset($credentials['host'], $credentials['user'], $credentials['pass'], $credentials['name'])) {
            return false;
        }
        $socket = '';
        if (!empty($credentials['socket'])) {
            $socket = "\ndefine('DB_SOCKET', '" . $credentials['host'] . "');";
        }
        $rootPath = \PFAD_ROOT;
        if (\strpos(\PFAD_ROOT, '\\') !== false) {
            $rootPath = \str_replace('\\', '\\\\', $rootPath);
        }
        $config = "<?php
define('PFAD_ROOT', '" . $rootPath . "');
define('URL_SHOP', '" . \substr(URL_SHOP, 0, -1) . "');" .
            $socket . "
define('DB_HOST','" . $credentials['host'] . "');
define('DB_NAME','" . \addcslashes($credentials['name'], "'") . "');
define('DB_USER','" . \addcslashes($credentials['user'], "'") . "');
define('DB_PASS','" . \addcslashes($credentials['pass'], "'") . "');

define('BLOWFISH_KEY', '" . $blowfishKey . "');

define('EVO_COMPATIBILITY', false);

//enables printing of warnings/infos/errors for the shop frontend
define('SHOP_LOG_LEVEL', E_ALL);
//enables printing of warnings/infos/errors for the dbeS sync
define('SYNC_LOG_LEVEL', E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
//enables printing of warnings/infos/errors for the admin backend
define('ADMIN_LOG_LEVEL', E_ALL);
//enables printing of warnings/infos/errors for the smarty templates
define('SMARTY_LOG_LEVEL', E_ALL);
//excplicitly show/hide errors
ini_set('display_errors', 0);" . "\n";
        $file   = \fopen(\PFAD_ROOT . \PFAD_INCLUDES . 'config.JTL-Shop.ini.php', 'w');
        \fwrite($file, $config);
        \fclose($file);

        return true;
    }

    /**
     * @param string $url
     * @return string
     */
    private function parseMysqlDump(string $url): string
    {
        if ($this->db === null) {
            return 'noNiceDB';
        }
        $content = \file($url);
        $errors  = '';
        $query   = '';
        foreach ($content as $i => $line) {
            $tsl = \trim($line);
            if ($line !== ''
                && \strpos($tsl, '/*') !== 0
                && \strpos($tsl, '--') !== 0
                && \strpos($tsl, '#') !== 0
            ) {
                $query .= $line;
                if (\preg_match('/;\s*$/', $line)) {
                    $result = $this->db->executeQuery($query, ReturnType::QUERYSINGLE);
                    if (!$result) {
                        $this->responseStatus    = false;
                        $this->responseMessage[] = $this->db->getErrorMessage() .
                            ' Nr: ' . $this->db->getErrorCode() . ' in Zeile ' . $i . '<br>' . $query . '<br>';
                    }
                    $query = '';
                }
            }
        }

        return $errors;
    }

    /**
     * @return VueInstaller
     * @throws InvalidEntityNameException
     */
    private function insertUsers(): self
    {
        $adminLogin                    = new stdClass();
        $adminLogin->cLogin            = $this->post['admin']['name'];
        $adminLogin->cPass             = \md5($this->post['admin']['pass']);
        $adminLogin->cName             = 'Admin';
        $adminLogin->cMail             = '';
        $adminLogin->kAdminlogingruppe = 1;
        $adminLogin->nLoginVersuch     = 0;
        $adminLogin->bAktiv            = 1;
        if (isset($this->post['admin']['locale']) && $this->post['admin']['locale'] === 'en') {
            $adminLogin->language = 'en-GB';
        }

        if (!$this->db->insertRow('tadminlogin', $adminLogin)) {
            $error                   = $this->db->getError();
            $this->responseMessage[] = 'Error code: ' . $this->db->getErrorCode();
            if (!\is_array($error)) {
                $this->responseMessage[] = $error;
            }
            $this->responseStatus = false;
        }

        $syncLogin        = new stdClass();
        $syncLogin->cMail = '';
        $syncLogin->cName = $this->post['wawi']['name'];
        $syncLogin->cPass = \password_hash($this->post['wawi']['pass'], \PASSWORD_DEFAULT);

        if (!$this->db->insertRow('tsynclogin', $syncLogin)) {
            $error                   = $this->db->getError();
            $this->responseMessage[] = 'Error code: ' . $this->db->getErrorCode();
            if (!\is_array($error)) {
                $this->responseMessage[] = $error;
            }
            $this->responseStatus = false;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function getDBCredentialsCheck(): self
    {
        $res        = new stdClass();
        $res->error = false;
        $res->msg   = 'connectionSuccess';
        if (isset($this->post['host'], $this->post['user'], $this->post['pass'], $this->post['name'])) {
            if (!empty($this->post['socket'])) {
                \define('DB_SOCKET', $this->post['socket']);
            }
            try {
                $db = new NiceDB($this->post['host'], $this->post['user'], $this->post['pass'], $this->post['name']);
                if (!$db->isConnected()) {
                    $res->error = true;
                    $res->msg   = 'cannotConnect';
                }
                $obj = $db->executeQuery("SHOW TABLES LIKE 'tsynclogin'", 1);
                if ($obj !== false) {
                    $res->error = true;
                    $res->msg   = 'shopExists';
                }
            } catch (Exception $e) {
                $res->error = true;
                $res->msg   = $e->getMessage();
            }
        } else {
            $res->msg   = 'noCredentials';
            $res->error = true;
        }
        $this->payload['msg']   = $res->msg;
        $this->payload['error'] = $res->error;

        return $this;
    }

    /**
     * @return $this
     */
    private function getIsInstalled(): self
    {
        $res = false;
        if (\file_exists(\PFAD_ROOT . \PFAD_INCLUDES . 'config.JTL-Shop.ini.php')) {
            //use buffer to avoid redeclaring constants errors
            \ob_start();
            require_once \PFAD_ROOT . \PFAD_INCLUDES . 'config.JTL-Shop.ini.php';
            \ob_end_clean();

            $res = \defined('BLOWFISH_KEY');
        }
        $this->payload['shopURL']   = URL_SHOP;
        $this->payload['installed'] = $res;

        return $this;
    }

    /**
     * @return $this
     */
    public function getSystemCheck(): self
    {
        $environment                  = new Environment();
        $this->payload['testresults'] = $environment->executeTestGroup('Shop5');

        return $this;
    }

    /**
     * @return $this
     */
    public function getDirectoryCheck(): self
    {
        $fsCheck                      = new Filesystem(\PFAD_ROOT);
        $this->payload['testresults'] = $fsCheck->getFoldersChecked();

        return $this;
    }

    /**
     * @param int    $length
     * @param string $seed
     * @return bool|string
     */
    private function getUID(int $length = 40, string $seed = ''): string
    {
        $uid      = '';
        $salt     = '';
        $saltBase = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789';
        for ($j = 0; $j < 30; $j++) {
            $salt .= \substr($saltBase, \random_int(0, \strlen($saltBase) - 1), 1);
        }
        $salt = \md5($salt);
        \mt_srand();
        if (\strlen($seed) > 0) {
            [$strings] = \explode(';', $seed);
            if (\is_array($strings) && \count($strings) > 0) {
                foreach ($strings as $string) {
                    $uid .= \md5($string . \md5(\PFAD_ROOT . (\time() - \random_int(0, \mt_getrandmax()))));
                }

                $uid = \md5($uid . $salt);
            } else {
                $sl = \strlen($seed);
                for ($i = 0; $i < $sl; $i++) {
                    $idx = \random_int(0, \strlen($seed) - 1);
                    if (((int)\date('w') % 2) <= \strlen($seed)) {
                        $idx = (int)\date('w') % 2;
                    }
                    $uid .= \md5(\substr($seed, $idx, 1) . $salt . \md5(\PFAD_ROOT . (\microtime(true) - \mt_rand())));
                }
            }
            $uid = $this->cryptPasswort($uid . $salt);
        } else {
            $uid = $this->cryptPasswort(\md5(\M_PI . $salt . \md5((string)(\time() - \mt_rand()))));
        }

        return $length > 0 ? \substr($uid, 0, $length) : $uid;
    }

    /**
     * @param string      $pass
     * @param null|string $hashPass
     * @return bool|string
     */
    private function cryptPasswort(string $pass, $hashPass = null)
    {
        $passLen = \strlen($pass);
        $salt    = \sha1(\uniqid((string)\random_int(\PHP_INT_MIN, \PHP_INT_MAX), true));
        $length  = \strlen($salt);
        $length  = \max($length >> 3, ($length >> 2) - $passLen);
        $salt    = $hashPass
            ? \substr($hashPass, \min($passLen, \strlen($hashPass) - $length), $length)
            : \strrev(\substr($salt, 0, $length));
        $hash    = \sha1($pass);
        $hash    = \sha1(\substr($hash, 0, $passLen) . $salt . \substr($hash, $passLen));
        $hash    = \substr($hash, $length);
        $hash    = \substr($hash, 0, $passLen) . $salt . \substr($hash, $passLen);

        return $hashPass && $hashPass !== $hash ? false : $hash;
    }
}
