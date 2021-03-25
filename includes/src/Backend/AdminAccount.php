<?php

namespace JTL\Backend;

use DateTime;
use Exception;
use JTL\Alert\Alert;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\L10n\GetText;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Mapper\AdminLoginStatusMessageMapper;
use JTL\Mapper\AdminLoginStatusToLogLevel;
use JTL\Model\AuthLogEntry;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Session\Backend;
use JTL\Shop;
use Psr\Log\LoggerInterface;
use stdClass;
use function Functional\reindex;

/**
 * Class AdminAccount
 * @package JTL\Backend
 */
class AdminAccount
{
    /**
     * @var bool
     */
    private $loggedIn = false;

    /**
     * @var bool
     */
    private $twoFaAuthenticated = false;

    /**
     * @var Loggerinterface
     */
    private $authLogger;

    /**
     * @var AdminLoginStatusToLogLevel
     */
    private $levelMapper;

    /**
     * @var AdminLoginStatusMessageMapper
     */
    private $messageMapper;

    /**
     * @var int
     */
    private $lockedMinutes = 0;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var GetText
     */
    private $getText;

    /**
     * @var AlertServiceInterface
     */
    private $alertService;

    /**
     * AdminAccount constructor.
     * @param DbInterface                   $db
     * @param LoggerInterface               $logger
     * @param AdminLoginStatusMessageMapper $statusMessageMapper
     * @param AdminLoginStatusToLogLevel    $levelMapper
     * @param GetText                       $getText
     * @param AlertServiceInterface         $alertService
     * @throws Exception
     */
    public function __construct(
        DbInterface $db,
        LoggerInterface $logger,
        AdminLoginStatusMessageMapper $statusMessageMapper,
        AdminLoginStatusToLogLevel $levelMapper,
        GetText $getText,
        AlertServiceInterface $alertService
    ) {
        $this->db            = $db;
        $this->authLogger    = $logger;
        $this->messageMapper = $statusMessageMapper;
        $this->levelMapper   = $levelMapper;
        $this->getText       = $getText;
        $this->alertService  = $alertService;
        Backend::getInstance();
        $this->initDefaults();
        $this->validateSession();
    }

    /**
     *
     */
    private function initDefaults(): void
    {
        if (!isset($_SESSION['AdminAccount'])) {
            $adminAccount              = new stdClass();
            $adminAccount->language    = $this->getText->getLanguage();
            $adminAccount->kAdminlogin = null;
            $adminAccount->oGroup      = null;
            $adminAccount->cLogin      = null;
            $adminAccount->cMail       = null;
            $adminAccount->cPass       = null;
            $adminAccount->attributes  = null;
            $_SESSION['AdminAccount']  = $adminAccount;
        }
    }

    /**
     * @return int
     */
    public function getLockedMinutes(): int
    {
        return $this->lockedMinutes;
    }

    /**
     * @param int $lockedMinutes
     */
    public function setLockedMinutes(int $lockedMinutes): void
    {
        $this->lockedMinutes = $lockedMinutes;
    }

    /**
     * checks user submitted hash against the ones saved in db
     *
     * @param string $hash - the hash received via email
     * @param string $mail - the admin account's email address
     * @return bool - true if successfully verified
     * @throws Exception
     */
    public function verifyResetPasswordHash(string $hash, string $mail): bool
    {
        $user = $this->db->select('tadminlogin', 'cMail', $mail);
        if ($user !== null) {
            // there should be a string <created_timestamp>:<hash> in the DB
            $timestampAndHash = \explode(':', $user->cResetPasswordHash);
            if (\count($timestampAndHash) === 2) {
                [$timeStamp, $originalHash] = $timestampAndHash;
                // check if the link is not expired (=24 hours valid)
                $createdAt = (new DateTime())->setTimestamp((int)$timeStamp);
                $now       = new DateTime();
                $diff      = $now->diff($createdAt);
                $secs      = ($diff->format('%a') * (60 * 60 * 24)); // total days
                $secs     += (int)$diff->format('%h') * (60 * 60); // hours
                $secs     += (int)$diff->format('%i') * 60; // minutes
                $secs     += (int)$diff->format('%s'); // seconds
                if ($secs > (60 * 60 * 24)) {
                    return false;
                }
                // check the submitted hash against the saved one
                return Shop::Container()->getPasswordService()->verify($hash, $originalHash);
            }
        }

        return false;
    }

    /**
     * creates hashes and sends mails for forgotten admin passwords
     *
     * @param string $email - the admin account's email address
     * @return bool - true if valid admin account
     * @throws Exception
     */
    public function prepareResetPassword(string $email): bool
    {
        $now  = (new DateTime())->format('U');
        $hash = \md5($email . Shop::Container()->getCryptoService()->randomString(30));
        $upd  = (object)['cResetPasswordHash' => $now . ':' . Shop::Container()->getPasswordService()->hash($hash)];
        $res  = $this->db->update('tadminlogin', 'cMail', $email, $upd);
        if ($res > 0) {
            $user                   = $this->db->select('tadminlogin', 'cMail', $email);
            $obj                    = new stdClass();
            $obj->passwordResetLink = Shop::getAdminURL() . '/pass.php?fpwh=' . $hash . '&mail=' . $email;
            $obj->cHash             = $hash;
            $obj->mail              = new stdClass();
            $obj->mail->toEmail     = $email;
            $obj->mail->toName      = $user->cLogin;

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_ADMINLOGIN_PASSWORT_VERGESSEN, $obj));

            $this->alertService->addAlert(Alert::TYPE_SUCCESS, __('successEmailSend'), 'successEmailSend');

            return true;
        }
        $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorEmailNotFound'), 'errorEmailNotFound');

        return false;
    }

    /**
     * @param int    $code
     * @param string $user
     * @return int
     */
    private function handleLoginResult(int $code, string $user): int
    {
        $log = new AuthLogEntry();

        $log->setIP(Request::getRealIP());
        $log->setCode($code);
        $log->setUser($user);

        $this->authLogger->log(
            $this->levelMapper->map($code),
            $this->messageMapper->map($code),
            $log->asArray()
        );

        return $code;
    }

    /**
     * @param string $cLogin
     * @param string $cPass
     * @return int
     * @throws Exception
     */
    public function login(string $cLogin, string $cPass): int
    {
        $admin = $this->db->select(
            'tadminlogin',
            'cLogin',
            $cLogin,
            null,
            null,
            null,
            null,
            false,
            '*, UNIX_TIMESTAMP(dGueltigBis) AS dGueltigTS'
        );
        if ($admin === null || !\is_object($admin)) {
            return $this->handleLoginResult(AdminLoginStatus::ERROR_USER_NOT_FOUND, $cLogin);
        }
        $admin->kAdminlogingruppe = (int)$admin->kAdminlogingruppe;
        if (!$admin->bAktiv && $admin->kAdminlogingruppe !== \ADMINGROUP) {
            return $this->handleLoginResult(AdminLoginStatus::ERROR_USER_DISABLED, $cLogin);
        }
        if ($admin->dGueltigTS && $admin->kAdminlogingruppe !== \ADMINGROUP && $admin->dGueltigTS < \time()) {
            return $this->handleLoginResult(AdminLoginStatus::ERROR_LOGIN_EXPIRED, $cLogin);
        }
        if ($admin->nLoginVersuch >= \MAX_LOGIN_ATTEMPTS && !empty($admin->locked_at)) {
            $time        = new DateTime($admin->locked_at);
            $diffMinutes = ((new DateTime('NOW'))->getTimestamp() - $time->getTimestamp()) / 60;
            if ($diffMinutes < \LOCK_TIME) {
                $this->setLockedMinutes((int)\ceil(\LOCK_TIME - $diffMinutes));

                return AdminLoginStatus::ERROR_LOCKED;
            }
        }
        $verified = false;
        $crypted  = null;
        if (\mb_strlen($admin->cPass) === 32) {
            if (\md5($cPass) !== $admin->cPass) {
                $this->setRetryCount($admin->cLogin);

                return $this->handleLoginResult(AdminLoginStatus::ERROR_INVALID_PASSWORD, $cLogin);
            }
            if (!isset($_SESSION['AdminAccount'])) {
                $_SESSION['AdminAccount'] = new stdClass();
            }
            $_SESSION['AdminAccount']->cPass  = \md5($cPass);
            $_SESSION['AdminAccount']->cLogin = $cLogin;
            $verified                         = true;
            if ($this->checkAndUpdateHash($cPass) === true) {
                $admin = $this->db->select(
                    'tadminlogin',
                    'cLogin',
                    $cLogin,
                    null,
                    null,
                    null,
                    null,
                    false,
                    '*, UNIX_TIMESTAMP(dGueltigBis) AS dGueltigTS'
                );
            }
        } elseif (\mb_strlen($admin->cPass) === 40) {
            // default login until Shop4
            $crypted = \cryptPasswort($cPass, $admin->cPass);
        } else {
            // new default login from 4.0 on
            $verified = \password_verify($cPass, $admin->cPass);
        }
        if ($verified === true || ($crypted !== null && $admin->cPass === $crypted)) {
            $settings = Shop::getSettings(\CONF_GLOBAL);
            if (\is_array($_SESSION)
                && $settings['global']['wartungsmodus_aktiviert'] === 'N'
                && \count($_SESSION) > 0
            ) {
                foreach (\array_keys($_SESSION) as $i) {
                    unset($_SESSION[$i]);
                }
            }
            if (!isset($admin->kSprache)) {
                $admin->kSprache = Shop::getLanguageID();
            }
            $admin->cISO       = Shop::Lang()->getIsoFromLangID((int)$admin->kSprache)->cISO;
            $admin->attributes = $this->getAttributes((int)$admin->kAdminlogin);
            $this->toSession($admin);
            $this->checkAndUpdateHash($cPass);
            if (!$this->getIsTwoFaAuthenticated()) {
                return $this->handleLoginResult(AdminLoginStatus::ERROR_TWO_FACTOR_AUTH_EXPIRED, $cLogin);
            }
            return $this->handleLoginResult($this->logged()
                ? AdminLoginStatus::LOGIN_OK
                : AdminLoginStatus::ERROR_NOT_AUTHORIZED, $cLogin);
        }

        $this->setRetryCount($admin->cLogin);

        return $this->handleLoginResult(AdminLoginStatus::ERROR_INVALID_PASSWORD, $cLogin);
    }

    /**
     * @param int $userID
     * @return mixed
     */
    private function getAttributes(int $userID)
    {
        // try, because of SHOP-4319
        try {
            $attributes = reindex($this->db->queryPrepared(
                'SELECT cName, cAttribText, cAttribValue FROM tadminloginattribut
                    WHERE kAdminlogin = :userID',
                ['userID' => $userID],
                ReturnType::ARRAY_OF_OBJECTS
            ), static function ($e) {
                return $e->cName;
            });
            if (!empty($attributes) && isset($attributes['useAvatarUpload'])) {
                $attributes['useAvatarUpload']->cAttribValue = Shop::getImageBaseURL() .
                    \ltrim($attributes['useAvatarUpload']->cAttribValue, '/');
            }
        } catch (Exception $e) {
            $attributes = null;
        }

        return $attributes;
    }

    /**
     * @return void
     */
    public function refreshAttributes(): void
    {
        $account = $this->account();
        if ($account !== false) {
            $account->attributes = $this->getAttributes($account->kAdminlogin);
        }
    }

    /**
     * @return $this
     */
    public function logout(): self
    {
        $this->loggedIn = false;
        \session_destroy();

        return $this;
    }

    /**
     * @return $this
     */
    public function lock(): self
    {
        $this->loggedIn = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function logged(): bool
    {
        return $this->getIsTwoFaAuthenticated() && $this->getIsAuthenticated();
    }

    /**
     * @return bool
     */
    public function getIsAuthenticated(): bool
    {
        return $this->loggedIn;
    }

    /**
     * @return bool
     */
    public function getIsTwoFaAuthenticated(): bool
    {
        return $this->twoFaAuthenticated;
    }

    /**
     * @param int $errCode
     */
    public function redirectOnFailure(int $errCode = 0): void
    {
        if (!$this->logged()) {
            $url = \strpos(\basename($_SERVER['REQUEST_URI']), 'logout.php') === false
                ? '?uri=' . \base64_encode(\basename($_SERVER['REQUEST_URI']))
                : '';
            if ($errCode !== 0) {
                $url .= (\mb_strpos($url, '?') === false ? '?' : '&') . 'errCode=' . $errCode;
            }
            \header('Location: index.php' . $url);
            exit();
        }
    }

    /**
     * @return bool|stdClass
     */
    public function account()
    {
        return $this->getIsAuthenticated() ? $_SESSION['AdminAccount'] : false;
    }

    /**
     * @param string $permission
     * @param bool   $redirectToLogin
     * @param bool   $showNoAccessPage
     * @return bool
     */
    public function permission($permission, bool $redirectToLogin = false, bool $showNoAccessPage = false): bool
    {
        if ($redirectToLogin) {
            $this->redirectOnFailure();
        }
        // grant full access to admin
        if ($this->account() !== false && (int)$this->account()->oGroup->kAdminlogingruppe === \ADMINGROUP) {
            return true;
        }
        $hasAccess = (isset($_SESSION['AdminAccount']->oGroup)
            && \is_object($_SESSION['AdminAccount']->oGroup)
            && \is_array($_SESSION['AdminAccount']->oGroup->oPermission_arr)
            && \in_array($permission, $_SESSION['AdminAccount']->oGroup->oPermission_arr, true));
        if ($showNoAccessPage && !$hasAccess) {
            Shop::Smarty()->display('tpl_inc/berechtigung.tpl');
            exit;
        }

        return $hasAccess;
    }

    /**
     *
     */
    public function redirectOnUrl(): void
    {
        $url    = Shop::getURL() . '/' . \PFAD_ADMIN . 'index.php';
        $parsed = \parse_url($url);
        $host   = $parsed['host'];
        if (!empty($parsed['port']) && (int)$parsed['port'] > 0) {
            $host .= ':' . $parsed['port'];
        }
        if (isset($_SERVER['HTTP_HOST']) && $host !== $_SERVER['HTTP_HOST'] && \mb_strlen($_SERVER['HTTP_HOST']) > 0) {
            \header('Location: ' . $url);
            exit;
        }
    }

    /**
     * @return $this
     */
    private function validateSession(): self
    {
        $this->loggedIn = false;
        if (isset($_SESSION['AdminAccount']->cLogin, $_SESSION['AdminAccount']->cPass, $_SESSION['AdminAccount']->cURL)
            && $_SESSION['AdminAccount']->cURL === \URL_SHOP
        ) {
            $account                  = $this->db->select(
                'tadminlogin',
                'cLogin',
                $_SESSION['AdminAccount']->cLogin,
                'cPass',
                $_SESSION['AdminAccount']->cPass
            );
            $this->twoFaAuthenticated = (isset($account->b2FAauth) && $account->b2FAauth === '1')
                ? (isset($_SESSION['AdminAccount']->TwoFA_valid) && $_SESSION['AdminAccount']->TwoFA_valid === true)
                : true;
            $this->loggedIn           = isset($account->cLogin);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function doTwoFA(): bool
    {
        if (isset($_SESSION['AdminAccount']->cLogin, $_POST['TwoFA_code'])) {
            $twoFA = new TwoFA($this->db);
            $twoFA->setUserByName($_SESSION['AdminAccount']->cLogin);
            $valid                                 = $twoFA->isCodeValid($_POST['TwoFA_code']);
            $this->twoFaAuthenticated              = $valid;
            $_SESSION['AdminAccount']->TwoFA_valid = $valid;

            return $valid;
        }

        return false;
    }

    /**
     * @return array
     */
    public function favorites(): array
    {
        return $this->logged()
            ? AdminFavorite::fetchAll($_SESSION['AdminAccount']->kAdminlogin)
            : [];
    }

    /**
     * @param stdClass $admin
     * @return $this
     */
    private function toSession($admin): self
    {
        $group = $this->getPermissionsByGroup($admin->kAdminlogingruppe);
        if (\is_object($group) || (int)$admin->kAdminlogingruppe === \ADMINGROUP) {
            $_SESSION['AdminAccount']              = new stdClass();
            $_SESSION['AdminAccount']->cURL        = \URL_SHOP;
            $_SESSION['AdminAccount']->kAdminlogin = (int)$admin->kAdminlogin;
            $_SESSION['AdminAccount']->cLogin      = $admin->cLogin;
            $_SESSION['AdminAccount']->cMail       = $admin->cMail;
            $_SESSION['AdminAccount']->cPass       = $admin->cPass;
            $_SESSION['AdminAccount']->language    = $admin->language ?? 'de-DE';
            $_SESSION['AdminAccount']->attributes  = $admin->attributes;

            if (!\is_object($group)) {
                $group                    = new stdClass();
                $group->kAdminlogingruppe = \ADMINGROUP;
            }

            $_SESSION['AdminAccount']->oGroup = $group;

            $this->setLastLogin($admin->cLogin)
                 ->setRetryCount($admin->cLogin, true)
                 ->validateSession();
        }

        return $this;
    }

    /**
     * @param string $cLogin
     * @return $this
     */
    private function setLastLogin($cLogin): self
    {
        $this->db->update('tadminlogin', 'cLogin', $cLogin, (object)['dLetzterLogin' => 'NOW()']);

        return $this;
    }

    /**
     * @param string $cLogin
     * @param bool   $bReset
     * @return $this
     */
    private function setRetryCount(string $cLogin, bool $bReset = false): self
    {
        if ($bReset) {
            $this->db->update(
                'tadminlogin',
                'cLogin',
                $cLogin,
                (object)['nLoginVersuch' => 0, 'locked_at' => '_DBNULL_']
            );

            return $this;
        }
        $this->db->queryPrepared(
            'UPDATE tadminlogin
                SET nLoginVersuch = nLoginVersuch+1
                WHERE cLogin = :login',
            ['login' => $cLogin],
            ReturnType::AFFECTED_ROWS
        );
        $data   = $this->db->select('tadminlogin', 'cLogin', $cLogin);
        $locked = (int)$data->nLoginVersuch >= \MAX_LOGIN_ATTEMPTS;
        if ($locked === true && \array_key_exists('locked_at', (array)$data)) {
            $this->db->update('tadminlogin', 'cLogin', $cLogin, (object)['locked_at' => 'NOW()']);
        }

        return $this;
    }

    /**
     * @param int $groupID
     * @return bool|object
     */
    private function getPermissionsByGroup(int $groupID)
    {
        $group = $this->db->select(
            'tadminlogingruppe',
            'kAdminlogingruppe',
            $groupID
        );
        if ($group !== null && isset($group->kAdminlogingruppe)) {
            $group->kAdminlogingruppe = (int)$group->kAdminlogingruppe;
            $permissions              = $this->db->selectAll(
                'tadminrechtegruppe',
                'kAdminlogingruppe',
                $groupID,
                'cRecht'
            );
            $group->oPermission_arr   = [];
            foreach ($permissions as $permission) {
                $group->oPermission_arr[] = $permission->cRecht;
            }

            return $group;
        }

        return false;
    }

    /**
     * @param string $password
     * @return string
     * @deprecated since 5.0
     * @throws Exception
     */
    public static function generatePasswordHash(string $password): string
    {
        return Shop::Container()->getPasswordService()->hash($password);
    }

    /**
     * update password hash if necessary
     *
     * @param string $password
     * @return bool - true when hash was updated
     * @throws Exception
     */
    private function checkAndUpdateHash(string $password): bool
    {
        $passwordService = Shop::Container()->getPasswordService();
        // only update hash if the db update to 4.00+ was already executed
        if (isset($_SESSION['AdminAccount']->cPass, $_SESSION['AdminAccount']->cLogin)
            && $passwordService->needsRehash($_SESSION['AdminAccount']->cPass)
        ) {
            $this->db->update(
                'tadminlogin',
                'cLogin',
                $_SESSION['AdminAccount']->cLogin,
                (object)['cPass' => $passwordService->hash($password)]
            );

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return (int)$_SESSION['AdminAccount']->kAdminlogin;
    }

    /**
     * @return GetText
     */
    public function getGetText(): GetText
    {
        return $this->getText;
    }
}
