<?php declare(strict_types=1);
/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */

use JTL\Media\Image;
use JTL\Media\Manager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_IMAGES_VIEW', true, true);
$manager = new Manager(Shop::Container()->getDB(), Shop::Container()->getGetText());

$smarty->assign('items', $manager->getItems())
    ->assign('corruptedImagesByType', $manager->getCorruptedImages(Image::TYPE_PRODUCT, MAX_CORRUPTED_IMAGES))
    ->display('bilderverwaltung.tpl');
