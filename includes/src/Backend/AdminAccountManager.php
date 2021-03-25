<?php declare(strict_types=1);

namespace JTL\Backend;

use DateTime;
use Exception;
use JTL\Alert\Alert;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\pluck;
use function Functional\reindex;

/**
 * Class AdminAccountManager
 * @package JTL\Backend
 */
class AdminAccountManager
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * @var JTLSmarty
     */
    private $alertService;

    /**
     * @var array
     */
    private $messages = [
        'notice' => '',
        'error'  => ''
    ];

    /**
     * AdminAccountManager constructor.
     *
     * @param JTLSmarty $smarty
     * @param DbInterface $db
     * @param AlertServiceInterface $alertService
     */
    public function __construct(JTLSmarty $smarty, DbInterface $db, AlertServiceInterface $alertService)
    {
        $this->smarty       = $smarty;
        $this->db           = $db;
        $this->alertService = $alertService;
    }

    /**
     * @param int $adminID
     * @return null|stdClass
     */
    public function getAdminLogin(int $adminID): ?stdClass
    {
        return $this->db->select('tadminlogin', 'kAdminlogin', $adminID);
    }

    /**
     * @return array
     */
    public function getAdminList(): array
    {
        return $this->db->query(
            'SELECT * FROM tadminlogin
                LEFT JOIN tadminlogingruppe
                    ON tadminlogin.kAdminlogingruppe = tadminlogingruppe.kAdminlogingruppe
                ORDER BY kAdminlogin',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return array
     */
    public function getAdminGroups(): array
    {
        return $this->db->query(
            'SELECT tadminlogingruppe.*, COUNT(tadminlogin.kAdminlogingruppe) AS nCount
                FROM tadminlogingruppe
                LEFT JOIN tadminlogin
                    ON tadminlogin.kAdminlogingruppe = tadminlogingruppe.kAdminlogingruppe
                GROUP BY tadminlogingruppe.kAdminlogingruppe',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return array
     */
    public function getAdminDefPermissions(): array
    {
        global $adminMenu;

        $perms              = reindex($this->db->selectAll('tadminrecht', [], []), static function ($e) {
            return $e->cRecht;
        });
        $permissionsOrdered = [];

        foreach ($adminMenu as $rootName => $rootEntry) {
            $permMainTMP = [];
            foreach ($rootEntry->items as $secondName => $secondEntry) {
                if ($secondEntry === 'DYNAMIC_PLUGINS' || !empty($secondEntry->excludeFromAccessView)) {
                    continue;
                }
                if (\is_object($secondEntry)) {
                    if (!isset($perms[$secondEntry->permissions])) {
                        $perms[$secondEntry->permissions] = (object)['name' => $secondName];
                    } else {
                        $perms[$secondEntry->permissions]->name = $secondName;
                    }

                    $permMainTMP[] = (object)[
                        'name'       => $secondName,
                        'permissions' => [$perms[$secondEntry->permissions]]
                    ];
                    unset($perms[$secondEntry->permissions]);
                } else {
                    $permSecondTMP = [];
                    foreach ($secondEntry as $thirdName => $thirdEntry) {
                        if (!empty($thirdEntry->excludeFromAccessView)) {
                            continue;
                        }
                        if (!isset($perms[$thirdEntry->permissions])) {
                            $perms[$thirdEntry->permissions] = (object)['name' => $thirdName];
                        } else {
                            $perms[$thirdEntry->permissions]->name = $thirdName;
                        }
                        $permSecondTMP[] = $perms[$thirdEntry->permissions];
                        unset($perms[$thirdEntry->permissions]);
                    }
                    $permMainTMP[] = (object)[
                        'name'       => $secondName,
                        'permissions' => $permSecondTMP
                    ];
                }
            }
            $permissionsOrdered[] = (object)[
                'name'     => $rootName,
                'children' => $permMainTMP
            ];
        }
        if (!empty($perms)) {
            $permissionsOrdered[] = (object)[
                'name'     => __('noMenuItem'),
                'children' => [(object)[
                    'name'       => '',
                    'permissions' => $perms
                ]]
            ];
        }

        return $permissionsOrdered;
    }

    /**
     * @param int $groupID
     * @return null|stdClass
     */
    public function getAdminGroup(int $groupID): ?stdClass
    {
        return $this->db->select('tadminlogingruppe', 'kAdminlogingruppe', $groupID);
    }

    /**
     * @param int $groupID
     * @return array
     */
    public function getAdminGroupPermissions(int $groupID): array
    {
        return pluck($this->db->selectAll('tadminrechtegruppe', 'kAdminlogingruppe', $groupID), 'cRecht');
    }

    /**
     * @param string     $row
     * @param string|int $value
     * @return bool
     */
    public function getInfoInUse(string $row, $value): bool
    {
        return \is_object($this->db->select('tadminlogin', $row, $value));
    }

    /**
     * @param string $languageTag
     */
    public function changeAdminUserLanguage(string $languageTag): void
    {
        $_SESSION['AdminAccount']->language = $languageTag;
        $_SESSION['Sprachen']               = LanguageHelper::getInstance()->gibInstallierteSprachen();

        if (!empty($_COOKIE['JTLSHOP'])) {
            unset($_SESSION['frontendUpToDate']);
        }

        $this->db->update(
            'tadminlogin',
            'kAdminlogin',
            $_SESSION['AdminAccount']->kAdminlogin,
            (object)['language' => $languageTag]
        );
    }

    /**
     * @param int $adminID
     * @return array
     */
    public function getAttributes(int $adminID): array
    {
        $extAttribs = $this->db->selectAll(
            'tadminloginattribut',
            'kAdminlogin',
            $adminID,
            'kAttribut, cName, cAttribValue, cAttribText',
            'cName ASC'
        );

        return \array_column($extAttribs, null, 'cName');
    }

    /**
     * @param stdClass $account
     * @param array $extAttribs
     * @param array $errorMap
     * @return bool
     */
    public function saveAttributes(stdClass $account, array $extAttribs, array &$errorMap): bool
    {
        if (!\is_array($extAttribs)) {
            return true;
        }
        $result = true;
        $this->validateAccount($extAttribs);

        \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
            'oAccount' => $account,
            'type'     => 'VALIDATE',
            'attribs'  => &$extAttribs,
            'messages' => &$this->messages,
            'result'   => &$result
        ]);

        if ($result !== true) {
            $errorMap = \array_merge($errorMap, $result);

            return false;
        }

        $handledKeys = [];
        foreach ($extAttribs as $key => $value) {
            $key      = Text::filterXSS($key);
            $longText = null;
            if (\is_array($value) && count($value) > 0) {
                $shortText = Text::filterXSS($value[0]);
                if (count($value) > 1) {
                    $longText = $value[1];
                }
            } else {
                $shortText = Text::filterXSS($value);
            }
            if ($this->db->queryPrepared(
                'INSERT INTO tadminloginattribut (kAdminlogin, cName, cAttribValue, cAttribText)
                    VALUES (:loginID, :loginName, :attribVal, :attribText)
                    ON DUPLICATE KEY UPDATE
                    cAttribValue = :attribVal,
                    cAttribText = :attribText',
                [
                    'loginID'    => $account->kAdminlogin,
                    'loginName'  => $key,
                    'attribVal'  => $shortText,
                    'attribText' => $longText ?? null
                ],
                ReturnType::DEFAULT
            ) === 0) {
                $this->addError(\sprintf(__('errorKeyChange'), $key));
            }
            $handledKeys[] = $key;
        }
        // nicht (mehr) vorhandene Attribute löschen
        $this->db->query(
            'DELETE FROM tadminloginattribut
            WHERE kAdminlogin = ' . (int)$account->kAdminlogin . "
                AND cName NOT IN ('" . \implode("', '", $handledKeys) . "')",
            ReturnType::DEFAULT
        );

        $adminAccount = Shop::Container()->getAdminAccount();
        if ($account->kAdminlogin === $adminAccount->account()->kAdminlogin) {
            $adminAccount->refreshAttributes();
        }

        return true;
    }

    /**
     * @param array $attribs
     * @return array|bool
     */
    public function validateAccount(array &$attribs)
    {
        $result = true;

        if (!$attribs['useAvatar']) {
            $attribs['useAvatar'] = 'N';
        }

        if ($attribs['useAvatar'] === 'U') {
            if (isset($_FILES['extAttribs']) && !empty($_FILES['extAttribs']['name']['useAvatarUpload'])) {
                $attribs['useAvatarUpload'] = $this->uploadAvatarImage($_FILES['extAttribs'], 'useAvatarUpload');

                if ($attribs['useAvatarUpload'] === false) {
                    $this->addError(__('errorImageUpload'));

                    $result = ['useAvatarUpload' => 1];
                }
            } elseif (empty($attribs['useAvatarUpload'])) {
                $this->addError(__('errorImageMissing'));

                $result = ['useAvatarUpload' => 1];
            }
        } elseif (!empty($attribs['useAvatarUpload'])) {
            if (\is_file(\PFAD_ROOT . $attribs['useAvatarUpload'])) {
                \unlink(\PFAD_ROOT . $attribs['useAvatarUpload']);
            }
            $attribs['useAvatarUpload'] = '';
        }

        foreach (LanguageHelper::getAllLanguages() as $language) {
            $useVita_ISO = 'useVita_' . $language->cISO;
            if (!empty($attribs[$useVita_ISO])) {
                $shortText = Text::filterXSS($attribs[$useVita_ISO]);
                $longtText = $attribs[$useVita_ISO];

                if (\mb_strlen($shortText) > 255) {
                    $shortText = \mb_substr($shortText, 0, 250) . '...';
                }

                $attribs[$useVita_ISO] = [$shortText, $longtText];
            }
        }

        return $result;
    }

    /**
     * @param array $tmpFile
     * @param string $attribName
     * @return bool|string
     */
    public function uploadAvatarImage(array $tmpFile, string $attribName)
    {
        $imgType = \array_search($tmpFile['type'][$attribName], [
            \IMAGETYPE_JPEG => \image_type_to_mime_type(\IMAGETYPE_JPEG),
            \IMAGETYPE_PNG  => \image_type_to_mime_type(\IMAGETYPE_PNG),
            \IMAGETYPE_BMP  => \image_type_to_mime_type(\IMAGETYPE_BMP),
            \IMAGETYPE_GIF  => \image_type_to_mime_type(\IMAGETYPE_GIF),
        ], true);

        if ($imgType !== false) {
            $imagePath = \PFAD_MEDIA_IMAGE . 'avatare/';
            $imageName = \time() . '_' .\pathinfo($tmpFile['name'][$attribName], \PATHINFO_FILENAME)
                . \image_type_to_extension($imgType);
            if (\is_dir(\PFAD_ROOT . $imagePath)
                || (\mkdir(\PFAD_ROOT . $imagePath, 0755) && \is_dir(\PFAD_ROOT . $imagePath))
            ) {
                if (\move_uploaded_file($tmpFile['tmp_name'][$attribName], \PFAD_ROOT . $imagePath . $imageName)) {
                    return '/' . $imagePath . $imageName;
                }
            }
        }

        return false;
    }

    /**
     * @param stdClass $oAccount
     * @return bool
     */
    public function deleteAttributes(stdClass $oAccount): bool
    {
        return $this->db->delete(
            'tadminloginattribut',
            'kAdminlogin',
            (int)$oAccount->kAdminlogin
        ) >= 0;
    }

    /**
     * @return string
     */
    public function actionAccountLock(): string
    {
        $adminID = Request::postInt('id');
        $account = $this->db->select('tadminlogin', 'kAdminlogin', $adminID);
        if (!empty($account->kAdminlogin)
            && (int)$account->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin
        ) {
            $this->addError(__('errorSelfLock'));
        } elseif (\is_object($account)) {
            if ((int)$account->kAdminlogingruppe === \ADMINGROUP) {
                $this->addError(__('errorLockAdmin'));
            } else {
                $result = true;
                $this->db->update('tadminlogin', 'kAdminlogin', $adminID, (object)['bAktiv' => 0]);
                \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                    'oAccount' => $account,
                    'type'     => 'LOCK',
                    'attribs'  => null,
                    'messages' => &$this->messages,
                    'result'   => &$result
                ]);
                if ($result === true) {
                    $this->addNotice(__('successLock'));
                }
            }
        } else {
            $this->addError(__('errorUserNotFound'));
        }

        return 'index_redirect';
    }

    /**
     * @return string
     */
    public function actionAccountUnLock(): string
    {
        $adminID = Request::postInt('id');
        $account = $this->db->select('tadminlogin', 'kAdminlogin', $adminID);
        if (\is_object($account)) {
            $result = true;
            $this->db->update('tadminlogin', 'kAdminlogin', $adminID, (object)['bAktiv' => 1]);
            \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                'oAccount' => $account,
                'type'     => 'UNLOCK',
                'attribs'  => null,
                'messages' => &$this->messages,
                'result'   => &$result
            ]);
            if ($result === true) {
                $this->addNotice(__('successUnlocked'));
            }
        } else {
            $this->addError(__('errorUserNotFound'));
        }

        return 'index_redirect';
    }

    /**
     * @return string
     * @throws Exception
     */
    public function actionAccountEdit(): string
    {
        $_SESSION['AdminAccount']->TwoFA_valid = true;

        $adminID     = Request::postInt('id', null);
        $qrCode      = '';
        $knownSecret = '';
        if ($adminID !== null) {
            $twoFA = new TwoFA($this->db);
            $twoFA->setUserByID($adminID);

            if ($twoFA->is2FAauthSecretExist() === true) {
                $qrCode      = $twoFA->getQRcode();
                $knownSecret = $twoFA->getSecret();
            }
        }
        $this->smarty->assign('QRcodeString', $qrCode)
            ->assign('cKnownSecret', $knownSecret);

        if (isset($_POST['save'])) {
            $errors              = [];
            $tmpAcc              = new stdClass();
            $tmpAcc->kAdminlogin = Request::postInt('kAdminlogin');
            $tmpAcc->cName       = \htmlspecialchars(\trim($_POST['cName']), \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET);
            $tmpAcc->cMail       = \htmlspecialchars(\trim($_POST['cMail']), \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET);
            $tmpAcc->language    = $_POST['language'];
            $tmpAcc->cLogin      = \trim($_POST['cLogin']);
            $tmpAcc->cPass       = \trim($_POST['cPass']);
            $tmpAcc->b2FAauth    = Request::postInt('b2FAauth');
            $tmpAttribs          = $_POST['extAttribs'] ?? [];

            if (0 < \mb_strlen($_POST['c2FAsecret'])) {
                $tmpAcc->c2FAauthSecret = \trim($_POST['c2FAsecret']);
            }

            $validUntil = Request::postInt('dGueltigBisAktiv') === 1;
            if ($validUntil) {
                try {
                    $tmpAcc->dGueltigBis = new DateTime($_POST['dGueltigBis']);
                } catch (Exception $e) {
                    $tmpAcc->dGueltigBis = '';
                }
                if ($tmpAcc->dGueltigBis !== false && $tmpAcc->dGueltigBis !== '') {
                    $tmpAcc->dGueltigBis = $tmpAcc->dGueltigBis->format('Y-m-d H:i:s');
                }
            }
            $tmpAcc->kAdminlogingruppe = Request::postInt('kAdminlogingruppe');
            if ((bool)$tmpAcc->b2FAauth && !isset($tmpAcc->c2FAauthSecret)) {
                $errors['c2FAsecret'] = 1;
            }
            if (\mb_strlen($tmpAcc->cName) === 0) {
                $errors['cName'] = 1;
            }
            if (\mb_strlen($tmpAcc->cMail) === 0) {
                $errors['cMail'] = 1;
            } elseif (Text::filterEmailAddress($tmpAcc->cMail) === false) {
                $errors['cMail'] = 2;
                $this->alertService->addAlert(
                    Alert::TYPE_DANGER,
                    __('validationErrorIncorrectEmail'),
                    'validationErrorIncorrectEmail'
                );
            }
            if (\mb_strlen($tmpAcc->cPass) === 0 && $tmpAcc->kAdminlogin === 0) {
                $errors['cPass'] = 1;
            }
            if (\mb_strlen($tmpAcc->cLogin) === 0) {
                $errors['cLogin'] = 1;
            } elseif ($tmpAcc->kAdminlogin === 0 && $this->getInfoInUse('cLogin', $tmpAcc->cLogin)) {
                $errors['cLogin'] = 2;
            }
            if ($validUntil && $tmpAcc->kAdminlogingruppe !== \ADMINGROUP && \mb_strlen($tmpAcc->dGueltigBis) === 0) {
                $errors['dGueltigBis'] = 1;
            }
            if ($tmpAcc->kAdminlogin > 0) {
                $oldAcc     = $this->getAdminLogin($tmpAcc->kAdminlogin);
                $groupCount = (int)$this->db->query(
                    'SELECT COUNT(*) AS nCount
                        FROM tadminlogin
                        WHERE kAdminlogingruppe = 1',
                    ReturnType::SINGLE_OBJECT
                )->nCount;
                if ($oldAcc !== null
                    && (int)$oldAcc->kAdminlogingruppe === \ADMINGROUP
                    && (int)$tmpAcc->kAdminlogingruppe !== \ADMINGROUP
                    && $groupCount <= 1
                ) {
                    $errors['bMinAdmin'] = 1;
                }
            }
            if (\count($errors) > 0) {
                $this->smarty->assign('cError_arr', $errors);
                $this->addError(__('errorFillRequired'));
                if (isset($errors['bMinAdmin']) && $errors['bMinAdmin'] === 1) {
                    $this->addError(__('errorAtLeastOneAdmin'));
                }
            } elseif ($tmpAcc->kAdminlogin > 0) {
                if (!$validUntil) {
                    $tmpAcc->dGueltigBis = '_DBNULL_';
                }
                // if we change the current admin-user, we have to update his session-credentials too!
                if ((int)$tmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin
                    && $tmpAcc->cLogin !== $_SESSION['AdminAccount']->cLogin) {
                    $_SESSION['AdminAccount']->cLogin = $tmpAcc->cLogin;
                }
                if (\mb_strlen($tmpAcc->cPass) > 0) {
                    $tmpAcc->cPass = Shop::Container()->getPasswordService()->hash($tmpAcc->cPass);
                    // if we change the current admin-user, we have to update his session-credentials too!
                    if ((int)$tmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
                        $_SESSION['AdminAccount']->cPass = $tmpAcc->cPass;
                    }
                } else {
                    unset($tmpAcc->cPass);
                }

                $this->changeAdminUserLanguage($tmpAcc->language);

                if ($this->db->update('tadminlogin', 'kAdminlogin', $tmpAcc->kAdminlogin, $tmpAcc) >= 0
                    && $this->saveAttributes($tmpAcc, $tmpAttribs, $errors)
                ) {
                    $result = true;
                    \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                        'oAccount' => $tmpAcc,
                        'type'     => 'SAVE',
                        'attribs'  => &$tmpAttribs,
                        'messages' => &$this->messages,
                        'result'   => &$result
                    ]);
                    if ($result === true) {
                        $this->addNotice(__('successUserSave'));

                        return 'index_redirect';
                    }
                    $this->smarty->assign('cError_arr', \array_merge($errors, (array)$result));
                } else {
                    $this->addError(__('errorUserSave'));
                    $this->smarty->assign('cError_arr', $errors);
                }
            } else {
                unset($tmpAcc->kAdminlogin);
                $tmpAcc->bAktiv        = 1;
                $tmpAcc->nLoginVersuch = 0;
                $tmpAcc->dLetzterLogin = '_DBNULL_';
                if (!isset($tmpAcc->dGueltigBis) || \mb_strlen($tmpAcc->dGueltigBis) === 0) {
                    $tmpAcc->dGueltigBis = '_DBNULL_';
                }
                $tmpAcc->cPass = Shop::Container()->getPasswordService()->hash($tmpAcc->cPass);

                if (($tmpAcc->kAdminlogin = $this->db->insert('tadminlogin', $tmpAcc))
                    && $this->saveAttributes($tmpAcc, $tmpAttribs, $errors)
                ) {
                    $result = true;
                    \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                        'oAccount' => $tmpAcc,
                        'type'     => 'SAVE',
                        'attribs'  => &$tmpAttribs,
                        'messages' => &$this->messages,
                        'result'   => &$result
                    ]);
                    if ($result === true) {
                        $this->addNotice(__('successUserAdd'));

                        return 'index_redirect';
                    }
                    $this->smarty->assign('cError_arr', \array_merge($errors, (array)$result));
                } else {
                    $this->addError(__('errorUserAdd'));
                    $this->smarty->assign('cError_arr', $errors);
                }
            }

            $account    = &$tmpAcc;
            $extAttribs = [];
            foreach ($tmpAttribs as $key => $attrib) {
                $extAttribs[$key] = (object)[
                    'kAttribut'    => null,
                    'cName'        => $key,
                    'cAttribValue' => $attrib
                ];
            }
            if ((int)$account->kAdminlogingruppe === 1) {
                unset($account->kAdminlogingruppe);
            }
        } elseif ($adminID > 0) {
            $account    = $this->getAdminLogin($adminID);
            $extAttribs = $this->getAttributes($adminID);
        } else {
            $account    = new stdClass();
            $extAttribs = [];
        }

        $this->smarty->assign('attribValues', $extAttribs);

        $extContent = '';
        \executeHook(\HOOK_BACKEND_ACCOUNT_PREPARE_EDIT, [
            'oAccount' => $account,
            'smarty'   => $this->smarty,
            'attribs'  => $extAttribs,
            'content'  => &$extContent
        ]);

        $groupCount = (int)$this->db->query(
            'SELECT COUNT(*) AS nCount
                FROM tadminlogin
                WHERE kAdminlogingruppe = 1',
            ReturnType::SINGLE_OBJECT
        )->nCount;
        $this->smarty->assign('oAccount', $account)
            ->assign('nAdminCount', $groupCount)
            ->assign('extContent', $extContent);

        return 'account_edit';
    }

    /**
     * @return string
     */
    public function actionAccountDelete(): string
    {
        $adminID    = Request::postInt('id');
        $groupCount = (int)$this->db->query(
            'SELECT COUNT(*) AS nCount
                FROM tadminlogin
                WHERE kAdminlogingruppe = 1',
            ReturnType::SINGLE_OBJECT
        )->nCount;
        $account    = $this->db->select('tadminlogin', 'kAdminlogin', $adminID);

        if (isset($account->kAdminlogin)
            && (int)$account->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin
        ) {
            $this->addError(__('errorSelfDelete'));
        } elseif (\is_object($account)) {
            if ((int)$account->kAdminlogingruppe === \ADMINGROUP && $groupCount <= 1) {
                $this->addError(__('errorAtLeastOneAdmin'));
            } elseif ($this->deleteAttributes($account) &&
                $this->db->delete('tadminlogin', 'kAdminlogin', $adminID)) {
                $result = true;
                \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                    'oAccount' => $account,
                    'type'     => 'DELETE',
                    'attribs'  => null,
                    'messages' => &$this->messages,
                    'result'   => &$result
                ]);
                if ($result === true) {
                    $this->addNotice(__('successUserDelete'));
                }
            } else {
                $this->addError(__('errorUserDelete'));
            }
        } else {
            $this->addError(__('errorUserNotFound'));
        }

        return 'index_redirect';
    }

    /**
     * @return string
     */
    public function actionGroupEdit(): string
    {
        $debug   = isset($_POST['debug']);
        $groupID = Request::postInt('id', null);
        if (isset($_POST['save'])) {
            $errors                        = [];
            $adminGroup                    = new stdClass();
            $adminGroup->kAdminlogingruppe = Request::postInt('kAdminlogingruppe');
            $adminGroup->cGruppe           = \htmlspecialchars(
                \trim($_POST['cGruppe']),
                \ENT_COMPAT | \ENT_HTML401,
                \JTL_CHARSET
            );
            $adminGroup->cBeschreibung     = \htmlspecialchars(
                \trim($_POST['cBeschreibung']),
                \ENT_COMPAT | \ENT_HTML401,
                \JTL_CHARSET
            );
            $groupPermissions              = $_POST['perm'];

            if (\mb_strlen($adminGroup->cGruppe) === 0) {
                $errors['cGruppe'] = 1;
            }
            if (\mb_strlen($adminGroup->cBeschreibung) === 0) {
                $errors['cBeschreibung'] = 1;
            }
            if (\count($groupPermissions) === 0) {
                $errors['cPerm'] = 1;
            }
            if (\count($errors) > 0) {
                $this->smarty->assign('cError_arr', $errors)
                    ->assign('oAdminGroup', $adminGroup)
                    ->assign('cAdminGroupPermission_arr', $groupPermissions);

                if (isset($errors['cPerm'])) {
                    $this->addError(__('errorAtLeastOneRight'));
                } else {
                    $this->addError(__('errorFillRequired'));
                }
            } else {
                if ($adminGroup->kAdminlogingruppe > 0) {
                    $this->db->update(
                        'tadminlogingruppe',
                        'kAdminlogingruppe',
                        (int)$adminGroup->kAdminlogingruppe,
                        $adminGroup
                    );
                    $this->db->delete(
                        'tadminrechtegruppe',
                        'kAdminlogingruppe',
                        (int)$adminGroup->kAdminlogingruppe
                    );
                    $permission                    = new stdClass();
                    $permission->kAdminlogingruppe = (int)$adminGroup->kAdminlogingruppe;
                    foreach ($groupPermissions as $oAdminGroupPermission) {
                        $permission->cRecht = $oAdminGroupPermission;
                        $this->db->insert('tadminrechtegruppe', $permission);
                    }
                    $this->addNotice(__('successGroupEdit'));

                    return 'group_redirect';
                }
                unset($adminGroup->kAdminlogingruppe);
                $groupID = $this->db->insert('tadminlogingruppe', $adminGroup);
                $this->db->delete('tadminrechtegruppe', 'kAdminlogingruppe', $groupID);
                $permission                    = new stdClass();
                $permission->kAdminlogingruppe = $groupID;
                foreach ($groupPermissions as $oAdminGroupPermission) {
                    $permission->cRecht = $oAdminGroupPermission;
                    $this->db->insert('tadminrechtegruppe', $permission);
                }
                $this->addNotice(__('successGroupCreate'));

                return 'group_redirect';
            }
        } elseif ($groupID > 0) {
            if ((int)$groupID === 1) {
                \header('Location:  '
                    . Shop::getAdminURL() . '/benutzerverwaltung.php?action=group_view&token='
                    . $_SESSION['jtl_token']);
            }
            $this->smarty->assign('bDebug', $debug)
                ->assign('oAdminGroup', $this->getAdminGroup($groupID))
                ->assign('cAdminGroupPermission_arr', $this->getAdminGroupPermissions($groupID));
        }

        return 'group_edit';
    }

    /**
     * @return string
     */
    public function actionGroupDelete(): string
    {
        $groupID = Request::postInt('id');
        $data    = $this->db->queryPrepared(
            'SELECT COUNT(*) AS member_count
                FROM tadminlogin
                WHERE kAdminlogingruppe = :gid',
            ['gid' => $groupID],
            ReturnType::SINGLE_OBJECT
        );
        if ((int)$data->member_count !== 0) {
            $this->addError(__('errorGroupDeleteCustomer'));

            return 'group_redirect';
        }

        if ($groupID !== \ADMINGROUP) {
            $this->db->delete('tadminlogingruppe', 'kAdminlogingruppe', $groupID);
            $this->db->delete('tadminrechtegruppe', 'kAdminlogingruppe', $groupID);
            $this->addNotice(__('successGroupDelete'));
        } else {
            $this->addError(__('errorGroupDelete'));
        }

        return 'group_redirect';
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getNextAction(): string
    {
        $action = 'account_view';
        if (isset($_REQUEST['action']) && Form::validateToken()) {
            $action = $_REQUEST['action'];
        }
        switch ($action) {
            case 'account_lock':
                $action = $this->actionAccountLock();
                break;
            case 'account_unlock':
                $action = $this->actionAccountUnLock();
                break;
            case 'account_edit':
                $action = $this->actionAccountEdit();
                break;
            case 'account_delete':
                $action = $this->actionAccountDelete();
                break;
            case 'group_edit':
                $action = $this->actionGroupEdit();
                break;
            case 'group_delete':
                $action = $this->actionGroupDelete();
                break;
            case 'quick_change_language':
                $this->actionQuickChangeLanguage();
                break;
        }

        return $action;
    }

    /**
     *
     */
    public function actionQuickChangeLanguage(): void
    {
        $this->changeAdminUserLanguage(Request::verifyGPDataString('language'));
        \header('Location: ' . Request::verifyGPDataString('referer'));
    }

    /**
     * @param string $tab
     */
    public function benutzerverwaltungRedirect($tab = ''): void
    {
        if ($this->getNotice() !== '') {
            $_SESSION['benutzerverwaltung.notice'] = $this->getNotice();
        } else {
            unset($_SESSION['benutzerverwaltung.notice']);
        }
        if ($this->getError() !== '') {
            $_SESSION['benutzerverwaltung.error'] = $this->getError();
        } else {
            unset($_SESSION['benutzerverwaltung.error']);
        }

        $urlParams = null;
        if (!empty($tab)) {
            $urlParams = ['tab' => Text::filterXSS($tab)];
        }

        \header('Location: ' . Shop::getAdminURL() . '/benutzerverwaltung.php' . (\is_array($urlParams)
                ? '?' . \http_build_query($urlParams, '', '&')
                : ''));
        exit;
    }

    /**
     * @param string $step
     * @throws \SmartyException
     */
    public function finalize(string $step): void
    {
        if (isset($_SESSION['benutzerverwaltung.notice'])) {
            $this->messages['notice'] = $_SESSION['benutzerverwaltung.notice'];
            unset($_SESSION['benutzerverwaltung.notice']);
        }
        if (isset($_SESSION['benutzerverwaltung.error'])) {
            $this->messages['error'] = $_SESSION['benutzerverwaltung.error'];
            unset($_SESSION['benutzerverwaltung.error']);
        }
        switch ($step) {
            case 'account_edit':
                if (Request::postInt('id') > 0) {
                    $this->alertService->addAlert(
                        Alert::TYPE_WARNING,
                        __('warningPasswordResetAuth'),
                        'warningPasswordResetAuth'
                    );
                }
                $this->smarty->assign('oAdminGroup_arr', $this->getAdminGroups())
                    ->assign(
                        'languages',
                        Shop::Container()->getGetText()->getAdminLanguages()
                    );
                break;
            case 'account_view':
                $this->smarty->assign('oAdminList_arr', $this->getAdminList())
                    ->assign('oAdminGroup_arr', $this->getAdminGroups());
                break;
            case 'group_edit':
                $this->smarty->assign('permissions', $this->getAdminDefPermissions());
                break;
            case 'index_redirect':
                $this->benutzerverwaltungRedirect('account_view');
                break;
            case 'group_redirect':
                $this->benutzerverwaltungRedirect('group_view');
                break;
        }

        $this->alertService->addAlert(Alert::TYPE_NOTE, $this->getNotice(), 'userManagementNote');
        $this->alertService->addAlert(Alert::TYPE_ERROR, $this->getError(), 'userManagementError');

        $this->smarty->assign('action', $step)
            ->assign('cTab', Text::filterXSS(Request::verifyGPDataString('tab')))
            ->display('benutzer.tpl');
    }

    /**
     * @param string $error
     */
    public function addError(string $error): void
    {
        $this->messages['error'] .= $error;
    }

    /**
     * @param string $notice
     */
    public function addNotice(string $notice): void
    {
        $this->messages['notice'] .= $notice;
    }

    /**
     * @return string
     */
    public function getNotice(): string
    {
        return $this->messages['notice'];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->messages['error'];
    }
}
