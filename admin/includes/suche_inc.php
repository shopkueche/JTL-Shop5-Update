<?php

use Illuminate\Support\Collection;
use JTL\Plugin\Admin\Listing;
use JTL\Plugin\Admin\ListingItem;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Shop;
use JTL\XMLParser;

/**
 * Search for backend settings
 *
 * @param string $query - search string
 * @param bool   $standalonePage - render as standalone page
 * @return string|null
 */
function adminSearch($query, $standalonePage = false): ?string
{
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';

    $adminMenuItems  = adminMenuSearch($query);
    $settings        = bearbeiteEinstellungsSuche($query);
    $shippings       = getShippingByName($query);
    $paymentMethods  = getPaymentMethodsByName($query);
    $groupedSettings = [];
    $currentGroup    = null;

    foreach ($settings->oEinstellung_arr as $setting) {
        if ($setting->cConf === 'N') {
            $currentGroup                   = $setting;
            $currentGroup->oEinstellung_arr = [];
            $groupedSettings[]              = $currentGroup;
        } elseif ($currentGroup !== null) {
            $setting->cName                   = highlightSearchTerm($setting->cName, $query);
            $currentGroup->oEinstellung_arr[] = $setting;
        }
    }

    foreach ($shippings as $shipping) {
        $shipping->cName = highlightSearchTerm($shipping->cName, $query);
    }

    foreach ($paymentMethods as $paymentMethod) {
        $paymentMethod->cName = highlightSearchTerm($paymentMethod->cName, $query);
    }

    Shop::Smarty()
        ->assign('standalonePage', $standalonePage)
        ->assign('query', $query)
        ->assign('adminMenuItems', $adminMenuItems)
        ->assign('settings', !empty($settings->oEinstellung_arr) ? $groupedSettings : null)
        ->assign('shippings', count($shippings) > 0 ? $shippings : null)
        ->assign('paymentMethods', count($paymentMethods) > 0 ? $paymentMethods : null)
        ->assign('plugins', getPlugins($query));

    if ($standalonePage) {
        Shop::Smarty()->display('suche.tpl');
        return null;
    }

    return Shop::Smarty()->fetch('suche.tpl');
}

/**
 * @param string $query
 * @return array
 */
function adminMenuSearch($query)
{
    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_menu.php';

    global $adminMenu;

    $results = [];

    foreach ($adminMenu as $menuName => $menu) {
        foreach ($menu->items as $subMenuName => $subMenu) {
            if (is_array($subMenu)) {
                foreach ($subMenu as $itemName => $item) {
                    if (is_object($item) && (
                            stripos($itemName, $query) !== false
                            || stripos($subMenuName, $query) !== false
                            || stripos($menuName, $query) !== false
                        )
                    ) {
                        $name      = $itemName;
                        $path      = $menuName . ' > ' . $subMenuName . ' > ' . $name;
                        $path      = highlightSearchTerm($path, $query);
                        $results[] = (object)[
                            'title' => $itemName,
                            'path'  => $path,
                            'link'  => $item->link,
                            'icon'  => $menu->icon
                        ];
                    }
                }
            } elseif (is_object($subMenu)
                && (stripos($subMenuName, $query) !== false
                    || stripos($menuName, $query) !== false
                )
            ) {
                $results[] = (object)[
                    'title' => $subMenuName,
                    'path'  => highlightSearchTerm($menuName . ' > ' . $subMenuName, $query),
                    'link'  => $subMenu->link,
                    'icon'  => $menu->icon
                ];
            }
        }
    }

    return $results;
}

/**
 * @param $haystack
 * @param $needle
 * @return string
 */
function highlightSearchTerm($haystack, $needle)
{
    return preg_replace(
        '/\p{L}*?' . preg_quote($needle, '/'). '\p{L}*/ui',
        '<mark>$0</mark>',
        $haystack
    );
}

/**
 * @param string $query
 * @return Collection
 */
function getPlugins(string $query): Collection
{
    if (mb_strlen($query) <= 2) {
        return new Collection();
    }
    $db               = Shop::Container()->getDB();
    $cache            = Shop::Container()->getCache();
    $parser           = new XMLParser();
    $legacyValidator  = new LegacyPluginValidator($db, $parser);
    $pluginValidator  = new PluginValidator($db, $parser);
    $listing          = new Listing($db, $cache, $legacyValidator, $pluginValidator);
    $pluginsInstalled = $listing->getInstalled();

    return $pluginsInstalled->filter(static function (ListingItem $e) use ($query) {
        if (stripos($e->getName(), $query) !== false) {
            $e->setName(highlightSearchTerm($e->getName(), $query));
            return true;
        }

        return false;
    });
}
