<?php declare(strict_types=1);

namespace JTL\License;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use JTL\Alert\Alert;
use JTL\Backend\AuthToken;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\License\Exception\ApiResultCodeException;
use JTL\License\Exception\ChecksumValidationException;
use JTL\License\Exception\DownloadValidationException;
use JTL\License\Exception\FilePermissionException;
use JTL\License\Installer\Helper;
use JTL\License\Struct\ExsLicense;
use JTL\Mapper\PluginValidation;
use JTL\Plugin\InstallCode;
use JTL\Session\Backend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use stdClass;

/**
 * Class Admin
 * @package JTL\License
 */
class Admin
{
    public const ACTION_EXTEND = 'extendLicense';

    public const ACTION_UPGRADE = 'upgradeLicense';

    public const ACTION_SET_BINDING = 'setbinding';

    public const ACTION_CLEAR_BINDING = 'clearbinding';

    public const ACTION_ENTER_TOKEN = 'entertoken';

    public const ACTION_SAVE_TOKEN = 'savetoken';

    public const ACTION_RECHECK = 'recheck';

    public const ACTION_REVOKE = 'revoke';

    public const ACTION_REDIRECT = 'redirect';

    public const ACTION_UPDATE = 'update';

    public const ACTION_INSTALL = 'install';

    public const STATE_APPROVED = 'approved';

    public const STATE_CREATED = 'created';

    public const STATE_FAILED = 'failed';

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var Checker
     */
    private $checker;

    /**
     * @var AuthToken
     */
    private $auth;

    /**
     * @var string[]
     */
    private $validActions = [
        self::ACTION_EXTEND,
        self::ACTION_UPGRADE,
        self::ACTION_SET_BINDING,
        self::ACTION_CLEAR_BINDING,
        self::ACTION_RECHECK,
        self::ACTION_REVOKE,
        self::ACTION_REDIRECT,
        self::ACTION_UPDATE,
        self::ACTION_ENTER_TOKEN,
        self::ACTION_SAVE_TOKEN,
        self::ACTION_INSTALL
    ];

    /**
     * Admin constructor.
     * @param Manager           $manager
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param Checker           $checker
     */
    public function __construct(Manager $manager, DbInterface $db, JTLCacheInterface $cache, Checker $checker)
    {
        $this->manager = $manager;
        $this->db      = $db;
        $this->cache   = $cache;
        $this->checker = $checker;
        $this->auth    = AuthToken::getInstance($this->db);
    }

    public function handleAuth(): void
    {
        $this->auth->responseToken();
    }

    /**
     * @param JTLSmarty $smarty
     */
    public function handle(JTLSmarty $smarty): void
    {
        \ob_start();
        $action = Request::postVar('action');
        $valid  = Form::validateToken();
        if ($valid) {
            if ($action === self::ACTION_SAVE_TOKEN) {
                $this->saveToken();
                $action = null;
            }
            if ($action === self::ACTION_ENTER_TOKEN) {
                $this->setToken($smarty);
                return;
            }
            if ($action === self::ACTION_SET_BINDING) {
                $this->setBinding($smarty);
            }
            if ($action === self::ACTION_CLEAR_BINDING) {
                $this->clearBinding($smarty);
            }
            if ($action === self::ACTION_RECHECK) {
                $this->getLicenses(true);
                $this->getList($smarty);
                \header('Location: ' . Shop::getAdminURL() . '/licenses.php', true, 303);
                exit();
            }
            if ($action === self::ACTION_REVOKE) {
                $this->auth->revoke();
                $action = null;
            }
            if ($action === self::ACTION_EXTEND || $action === self::ACTION_UPGRADE) {
                $this->extendUpgrade($smarty, $action);
            }
        }
        if ($action === null || !\in_array($action, $this->validActions, true) || !$valid) {
            $this->getLicenses(true);
            $this->getList($smarty);
            return;
        }
        if ($action === self::ACTION_REDIRECT) {
            $this->auth->requestToken(
                Backend::get('jtl_token'),
                Shop::getAdminURL(true) . '/licenses.php?action=code'
            );
        }
        if ($action === self::ACTION_UPDATE || $action === self::ACTION_INSTALL) {
            $this->installUpdate($action, $smarty);
        }
    }

    /**
     * @param string    $action
     * @param JTLSmarty $smarty
     */
    private function installUpdate(string $action, JTLSmarty $smarty): void
    {
        $itemID           = Request::postVar('item-id', '');
        $exsID            = Request::postVar('exs-id', '');
        $type             = Request::postVar('license-type', '');
        $response         = new AjaxResponse();
        $response->action = $action;
        $response->id     = $itemID;
        if ($type !== '') {
            $response->id .= '-' . $type;
        }
        try {
            $helper    = new Helper($this->manager, $this->db, $this->cache);
            $installer = $helper->getInstaller($itemID);
            $download  = $helper->getDownload($itemID);
            $result    = $action === self::ACTION_UPDATE
                ? $installer->update($exsID, $download, $response)
                : $installer->install($itemID, $download, $response, true);
            if ($result === InstallCode::DUPLICATE_PLUGIN_ID && $action !== self::ACTION_UPDATE) {
                $download = $helper->getDownload($itemID);
                $result   = $installer->forceUpdate($download, $response);
            }
            $this->cache->flushTags([\CACHING_GROUP_LICENSES]);
            if ($result !== InstallCode::OK) {
                $mapper         = new PluginValidation();
                $errorCode      = $result;
                $mappedErrorMsg = $mapper->map($result);
                if (empty($response->error)) {
                    $response->error = __('Error code: %d', $errorCode) . ' - ' . $mappedErrorMsg;
                }
                $smarty->assign('licenseErrorMessage', $response->error)
                    ->assign('mappedErrorMessage', $mappedErrorMsg)
                    ->assign('resultCode', $result);
            }
        } catch (ClientException
        | ConnectException
        | FilePermissionException
        | ApiResultCodeException
        | DownloadValidationException
        | ChecksumValidationException
        | InvalidArgumentException $e
        ) {
            $response->status = 'FAILED';
            $msg              = $e->getMessage();
            if (\strpos($msg, 'response:') !== false) {
                $msg = \substr($msg, 0, \strpos($msg, 'response:'));
            }
            $smarty->assign('licenseErrorMessage', $msg);
        }
        $this->getList($smarty);
        $license = $this->manager->getLicenseByItemID($itemID);
        if ($license === null || $license->getReferencedItem() === null) {
            $license = $this->manager->getLicenseByExsID($exsID);
        }
        if ($license !== null && $license->getReferencedItem() !== null) {
            $smarty->assign('license', $license);
            $response->html         = $smarty->fetch('tpl_inc/licenses_referenced_item.tpl');
            $response->notification = $smarty->fetch('tpl_inc/updates_drop.tpl');
        }
        $this->sendResponse($response);
    }

    /**
     * @param bool      $up
     * @param JTLSmarty $smarty
     */
    private function updateBinding(bool $up, JTLSmarty $smarty): void
    {
        $apiResponse      = '';
        $response         = new AjaxResponse();
        $response->action = $up === true ? 'setbinding' : 'clearbinding';
        try {
            $apiResponse = $up === true
                ? $this->manager->setBinding(Request::postVar('url'))
                : $this->manager->clearBinding(Request::postVar('url'));
        } catch (ClientException | GuzzleException $e) {
            $response->error = $e->getMessage();
            if ($e->getResponse()->getStatusCode() === 400) {
                $body = \json_decode((string)$e->getResponse()->getBody());
                if (isset($body->code, $body->message) && $body->code === 422) {
                    $response->error = $body->message;
                }
            }
            $smarty->assign('bindErrorMessage', $response->error);
        }
        $this->getLicenses(true);
        $this->getList($smarty);
        $response->replaceWith['#unbound-licenses'] = $smarty->fetch('tpl_inc/licenses_unbound.tpl');
        $response->replaceWith['#bound-licenses']   = $smarty->fetch('tpl_inc/licenses_bound.tpl');
        $response->html                             = $apiResponse;
        $this->sendResponse($response);
    }

    /**
     * @param JTLSmarty $smarty
     * @param string    $action
     * @throws \SmartyException
     */
    private function extendUpgrade(JTLSmarty $smarty, string $action): void
    {
        $responseData     = null;
        $apiResponse      = '';
        $response         = new AjaxResponse();
        $response->action = $action;
        try {
            $apiResponse  = $this->manager->extendUpgrade(
                Request::postVar('url'),
                Request::postVar('exsid'),
                Request::postVar('key')
            );
            $responseData = \json_decode($apiResponse);
        } catch (ClientException | GuzzleException $e) {
            $response->error = $e->getMessage();
            $smarty->assign('extendErrorMessage', $e->getMessage());
        }
        if (isset($responseData->state)) {
            if ($responseData->state === self::STATE_APPROVED) {
                if ($action === self::ACTION_EXTEND) {
                    $smarty->assign('extendSuccessMessage', 'Successfully extended.');
                } elseif ($action === self::ACTION_UPGRADE) {
                    $smarty->assign('extendSuccessMessage', 'Successfully executed.');
                }
            } elseif ($responseData->state === self::STATE_FAILED && isset($responseData->failure_reason)) {
                $smarty->assign('extendErrorMessage', $responseData->failure_reason);
            } elseif ($responseData->state === self::STATE_CREATED
                && isset($responseData->links)
                && \is_array($responseData->links)
            ) {
                foreach ($responseData->links as $link) {
                    if (isset($link->rel) && $link->rel === 'redirect_url') {
                        $response->redirect = $link->href;
                        $response->status   = 'OK';
                        $this->sendResponse($response);
                    }
                }
            }
        }
        $this->getLicenses(true);
        $this->getList($smarty);
        $response->replaceWith['#bound-licenses'] = $smarty->fetch('tpl_inc/licenses_bound.tpl');
        $response->html                           = $apiResponse;
        $this->sendResponse($response);
    }

    /**
     * @param JTLSmarty $smarty
     */
    private function setToken(JTLSmarty $smarty): void
    {
        $smarty->assign('setToken', true)
            ->assign('hasAuth', false);
    }

    private function saveToken(): void
    {
        $code  = \trim(Request::postVar('code', ''));
        $token = \trim(Request::postVar('token', ''));
        $this->auth->reset($code);
        AuthToken::getInstance($this->db)->set($code, $token);
    }

    /**
     * @param JTLSmarty $smarty
     */
    private function setBinding(JTLSmarty $smarty): void
    {
        $this->updateBinding(true, $smarty);
    }

    /**
     * @param JTLSmarty $smarty
     */
    private function clearBinding(JTLSmarty $smarty): void
    {
        $this->updateBinding(false, $smarty);
    }

    /**
     * @param JTLSmarty $smarty
     */
    private function setOverviewData(JTLSmarty $smarty): void
    {
        $data = $this->manager->getLicenseData();
        $smarty->assign('hasAuth', $this->auth->isValid())
            ->assign('lastUpdate', $data->timestamp ?? null);
    }

    /**
     * @param bool $force
     */
    private function getLicenses(bool $force = false): void
    {
        if (!$this->auth->isValid()) {
            return;
        }
        try {
            $this->manager->update($force, $this->getInstalledExtensionPostData());
            $this->checker->handleExpiredLicenses($this->manager);
        } catch (RequestException | Exception | ClientException $e) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                __('errorFetchLicenseAPI') . '' . $e->getMessage(),
                'errorFetchLicenseAPI'
            );
        }
    }

    /**
     * @return array
     */
    private function getInstalledExtensionPostData(): array
    {
        $mapper     = new Mapper($this->manager);
        $collection = $mapper->getCollection();
        $data       = [];
        foreach ($collection as $exsLicense) {
            /** @var ExsLicense $exsLicense */
            $avail         = $exsLicense->getReleases()->getAvailable();
            $item          = new stdClass();
            $item->active  = false;
            $item->id      = $exsLicense->getID();
            $item->exsid   = $exsLicense->getExsID();
            $item->version = $avail !== null ? (string)$avail->getVersion() : '0.0.0';
            $reference     = $exsLicense->getReferencedItem();
            if ($reference !== null && $reference->getInstalledVersion() !== null) {
                $item->active  = $reference->isActive();
                $item->version = (string)$reference->getInstalledVersion();
                if ($reference->getDateInstalled() !== null) {
                    $item->enabled = $reference->getDateInstalled();
                }
            }
            $data[] = $item;
        }

        return $data;
    }

    /**
     * @param JTLSmarty $smarty
     */
    private function getList(JTLSmarty $smarty): void
    {
        $this->setOverviewData($smarty);
        $mapper     = new Mapper($this->manager);
        $collection = $mapper->getCollection();
        $smarty->assign('licenses', $collection)
            ->assign('authToken', $this->auth->get())
            ->assign('rawData', isset($_GET['debug']) ? $this->manager->getLicenseData() : null)
            ->assign('licenseItemUpdates', $collection->getUpdateableItems());
    }

    /**
     * @param AjaxResponse $response
     */
    private function sendResponse(AjaxResponse $response): void
    {
        \ob_clean();
        \ob_start();
        echo \json_encode($response);
        echo \ob_get_clean();
        exit;
    }
}
