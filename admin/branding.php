<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Media\Media;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */

$oAccount->permission('DISPLAY_BRANDING_VIEW', true, true);
$step        = 'branding_uebersicht';
$alertHelper = Shop::Container()->getAlertService();
if (isset($_POST['action']) && $_POST['action'] === 'delete' && Form::validateToken()) {
    $id = (int)$_POST['id'];
    loescheBrandingBild($id);
    $response         = new stdClass();
    $response->id     = $id;
    $response->status = 'OK';
    die(json_encode($response));
}
if (Request::verifyGPCDataInt('branding') === 1) {
    $step = 'branding_detail';
    if (Request::postInt('speicher_einstellung') === 1 && Form::validateToken()) {
        if (speicherEinstellung(Request::verifyGPCDataInt('kBranding'), $_POST, $_FILES)) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
        }
    }
    if (Request::verifyGPCDataInt('kBranding') > 0) {
        $smarty->assign('oBranding', gibBranding(Request::verifyGPCDataInt('kBranding')));
    }
} else {
    $smarty->assign('oBranding', gibBranding(1));
}

$smarty->assign('cRnd', time())
       ->assign('oBranding_arr', gibBrandings())
       ->assign('PFAD_BRANDINGBILDER', PFAD_BRANDINGBILDER)
       ->assign('step', $step)
       ->display('branding.tpl');

/**
 * @return mixed
 */
function gibBrandings()
{
    return Shop::Container()->getDB()->selectAll('tbranding', [], [], '*', 'cBildKategorie');
}

/**
 * @param int $brandingID
 * @return mixed
 */
function gibBranding(int $brandingID)
{
    return Shop::Container()->getDB()->queryPrepared(
        'SELECT tbranding.*, tbranding.kBranding AS kBrandingTMP, tbrandingeinstellung.*
            FROM tbranding
            LEFT JOIN tbrandingeinstellung 
                ON tbrandingeinstellung.kBranding = tbranding.kBranding
            WHERE tbranding.kBranding = :bid
            GROUP BY tbranding.kBranding',
        ['bid' => $brandingID],
        ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int   $brandingID
 * @param array $post
 * @param array $files
 * @return bool
 */
function speicherEinstellung(int $brandingID, array $post, array $files)
{
    $db                 = Shop::Container()->getDB();
    $conf               = new stdClass();
    $conf->dRandabstand = 0;
    $conf->kBranding    = $brandingID;
    $conf->cPosition    = $post['cPosition'];
    $conf->nAktiv       = $post['nAktiv'];
    $conf->dTransparenz = $post['dTransparenz'];
    $conf->dGroesse     = $post['dGroesse'];

    if (mb_strlen($files['cBrandingBild']['name']) > 0) {
        $conf->cBrandingBild = 'kBranding_' . $brandingID . mappeFileTyp($files['cBrandingBild']['type']);
    } else {
        $tmpConf             = $db->select(
            'tbrandingeinstellung',
            'kBranding',
            $brandingID
        );
        $conf->cBrandingBild = !empty($tmpConf->cBrandingBild)
            ? $tmpConf->cBrandingBild
            : '';
    }

    if ($conf->kBranding > 0 && mb_strlen($conf->cPosition) > 0 && mb_strlen($conf->cBrandingBild) > 0) {
        // Alte Einstellung loeschen
        $db->delete('tbrandingeinstellung', 'kBranding', $brandingID);
        if (mb_strlen($files['cBrandingBild']['name']) > 0) {
            loescheBrandingBild($conf->kBranding);
            speicherBrandingBild($files, $conf->kBranding);
        }
        $db->insert('tbrandingeinstellung', $conf);
        $data = $db->select('tbranding', 'kBranding', $conf->kBranding);
        $type = Media::getClass($data->cBildKategorie ?? '');
        $type::clearCache();
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);

        return true;
    }

    return false;
}

/**
 * @param array $files
 * @param int   $brandingID
 * @return bool
 */
function speicherBrandingBild($files, int $brandingID)
{
    if ($files['cBrandingBild']['type'] === 'image/jpeg'
        || $files['cBrandingBild']['type'] === 'image/pjpeg'
        || $files['cBrandingBild']['type'] === 'image/gif'
        || $files['cBrandingBild']['type'] === 'image/png'
        || $files['cBrandingBild']['type'] === 'image/bmp'
    ) {
        $upload = PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' .
            $brandingID . mappeFileTyp($files['cBrandingBild']['type']);

        return move_uploaded_file($files['cBrandingBild']['tmp_name'], $upload);
    }

    return false;
}

/**
 * @param int $brandingID
 */
function loescheBrandingBild(int $brandingID)
{
    if (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $brandingID . '.jpg')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $brandingID . '.jpg');
    } elseif (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $brandingID . '.png')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $brandingID . '.png');
    } elseif (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $brandingID . '.gif')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $brandingID . '.gif');
    } elseif (file_exists(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $brandingID . '.bmp')) {
        @unlink(PFAD_ROOT . PFAD_BRANDINGBILDER . 'kBranding_' . $brandingID . '.bmp');
    }
}

/**
 * @param string $ype
 * @return string
 */
function mappeFileTyp(string $ype)
{
    switch ($ype) {
        case 'image/gif':
            return '.gif';
        case 'image/png':
            return '.png';
        case 'image/bmp':
            return '.bmp';
        case 'image/jpeg':
        case 'image/pjpeg':
        default:
            return '.jpg';
    }
}
