<?php

use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\IO\IOError;
use JTL\Shop;
use JTL\Shopsetting;

/**
 * @param string $index
 * @param string $create
 * @return array|IOError
 */
function createSearchIndex($index, $create)
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/sucheinstellungen');
    require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

    $index    = mb_convert_case(Text::xssClean($index), MB_CASE_LOWER);
    $notice   = '';
    $errorMsg = '';

    if (!in_array($index, ['tartikel', 'tartikelsprache'], true)) {
        return new IOError(__('errorIndexInvalid'), 403);
    }

    try {
        if (Shop::Container()->getDB()->query(
            "SHOW INDEX FROM $index WHERE KEY_NAME = 'idx_{$index}_fulltext'",
            ReturnType::SINGLE_OBJECT
        )) {
            Shop::Container()->getDB()->executeQuery(
                "ALTER TABLE $index DROP KEY idx_{$index}_fulltext",
                ReturnType::QUERYSINGLE
            );
        }
    } catch (Exception $e) {
        // Fehler beim Index löschen ignorieren
    }

    if ($create === 'Y') {
        $searchRows = array_map(static function ($item) {
            $items = explode('.', $item, 2);

            return $items[1];
        }, JTL\Filter\States\BaseSearchQuery::getSearchRows());

        switch ($index) {
            case 'tartikel':
                $rows = array_intersect(
                    $searchRows,
                    [
                        'cName',
                        'cSeo',
                        'cSuchbegriffe',
                        'cArtNr',
                        'cKurzBeschreibung',
                        'cBeschreibung',
                        'cBarcode',
                        'cISBN',
                        'cHAN',
                        'cAnmerkung'
                    ]
                );
                break;
            case 'tartikelsprache':
                $rows = array_intersect($searchRows, ['cName', 'cSeo', 'cKurzBeschreibung', 'cBeschreibung']);
                break;
            default:
                return new IOError(__('errorIndexInvalid'), 403);
        }

        try {
            Shop::Container()->getDB()->executeQuery(
                'UPDATE tsuchcache SET dGueltigBis = DATE_ADD(NOW(), INTERVAL 10 MINUTE)',
                ReturnType::QUERYSINGLE
            );
            $res = Shop::Container()->getDB()->executeQuery(
                "ALTER TABLE $index
                    ADD FULLTEXT KEY idx_{$index}_fulltext (" . implode(', ', $rows) . ')',
                ReturnType::QUERYSINGLE
            );
        } catch (Exception $e) {
            $res = 0;
        }

        if ($res === 0) {
            $errorMsg     = __('errorIndexNotCreatable');
            $shopSettings = Shopsetting::getInstance();
            $settings     = $shopSettings[Shopsetting::mapSettingName(CONF_ARTIKELUEBERSICHT)];

            if ($settings['suche_fulltext'] !== 'N') {
                $settings['suche_fulltext'] = 'N';
                saveAdminSectionSettings(CONF_ARTIKELUEBERSICHT, $settings);

                Shop::Container()->getCache()->flushTags([
                    CACHING_GROUP_OPTION,
                    CACHING_GROUP_CORE,
                    CACHING_GROUP_ARTICLE,
                    CACHING_GROUP_CATEGORY
                ]);
                $shopSettings->reset();
            }
        } else {
            $notice = sprintf(__('successIndexCreate'), $index);
        }
    } else {
        $notice = sprintf(__('successIndexDelete'), $index);
    }

    return $errorMsg !== '' ? new IOError($errorMsg) : ['hinweis' => $notice];
}

/**
 * @return array
 */
function clearSearchCache()
{
    Shop::Container()->getDB()->query('DELETE FROM tsuchcachetreffer', ReturnType::AFFECTED_ROWS);
    Shop::Container()->getDB()->query('DELETE FROM tsuchcache', ReturnType::AFFECTED_ROWS);
    Shop::Container()->getGetText()->loadAdminLocale('pages/sucheinstellungen');

    return ['hinweis' => __('successSearchCacheDelete')];
}
