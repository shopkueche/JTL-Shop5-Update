<?php

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */

$oAccount->permission('FILESYSTEM_VIEW', true, true);

use JTL\Alert\Alert;
use JTL\Filesystem\AdapterFactory;
use JTL\Filesystem\Filesystem;
use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Shopsetting;

$shopSettings = Shopsetting::getInstance();
$alertHelper  = Shop::Container()->getAlertService();

Shop::Container()->getGetText()->loadConfigLocales(true, true);

if (!empty($_POST) && Form::validateToken()) {
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, saveAdminSectionSettings(CONF_FS, $_POST), 'saveSettings');
    $shopSettings->reset();

    if (isset($_POST['test'])) {
        try {
            $config  = Shop::getSettings([CONF_FS])['fs'];
            $factory = new AdapterFactory($config);
            $factory->setFtpConfig([
                'ftp_host'     => $_POST['ftp_hostname'],
                'ftp_port'     => (int)($_POST['ftp_port'] ?? 21),
                'ftp_username' => $_POST['ftp_user'],
                'ftp_password' => $_POST['ftp_pass'],
                'ftp_ssl'      => (int)$_POST['ftp_ssl'] === 1,
                'ftp_root'     => $_POST['ftp_path']
            ]);
            $factory->setSftpConfig([
                'sftp_host'     => $_POST['sftp_hostname'],
                'sftp_port'     => (int)($_POST['sftp_port'] ?? 22),
                'sftp_username' => $_POST['sftp_user'],
                'sftp_password' => $_POST['sftp_pass'],
                'sftp_privkey'  => $_POST['sftp_privkey'],
                'sftp_root'     => $_POST['sftp_path']
            ]);
            $factory->setAdapter($_POST['fs_adapter']);
            $fs         = new Filesystem($factory->getAdapter());
            $isShopRoot = $fs->has('includes/config.JTL-Shop.ini.php');
            if ($isShopRoot) {
                $alertHelper->addAlert(Alert::TYPE_INFO, __('fsValidConnection'), 'fsValidConnection');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('fsInvalidShopRoot'), 'fsInvalidShopRoot');
            }
        } catch (Exception $e) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, $e->getMessage(), 'errorFS');
        }
    }
}
$config = getAdminSectionSettings(CONF_FS);
Shop::Container()->getGetText()->localizeConfigs($config);
$smarty->assign('oConfig_arr', $config)
    ->display('filesystem.tpl');
