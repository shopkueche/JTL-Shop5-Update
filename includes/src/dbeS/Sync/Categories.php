<?php

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\LastJob;
use JTL\dbeS\Starter;
use JTL\Helpers\Seo;
use JTL\Language\LanguageHelper;
use stdClass;
use function Functional\map;

/**
 * Class Categories
 * @package JTL\dbeS\Sync
 */
final class Categories extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        $categoryIDs = [];
        $this->db->query('START TRANSACTION', ReturnType::DEFAULT);
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (isset($xml['tkategorie attr']['nGesamt']) || isset($xml['tkategorie attr']['nAktuell'])) {
                unset($xml['tkategorie attr']['nGesamt'], $xml['tkategorie attr']['nAktuell']);
            }
            if (\strpos($file, 'katdel.xml') !== false) {
                $this->handleDeletes($xml);
            } else {
                $categoryIDs[] = $this->handleInserts($xml);
            }
        }
        $this->cache->flushTags(map($categoryIDs, static function ($categoryID) {
            return \CACHING_GROUP_CATEGORY . '_' . $categoryID;
        }));
        $lastJob = new LastJob($this->db, $this->logger);
        $lastJob->run(\LASTJOBS_KATEGORIEUPDATE, 'Kategorien_xml');
        $this->db->query('COMMIT', ReturnType::DEFAULT);

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleDeletes(array $xml): void
    {
        $source = $xml['del_kategorien']['kKategorie'] ?? null;
        if ($source === null) {
            return;
        }
        if (!\is_array($source) && \is_numeric($source)) {
            $source = [$source];
        }
        if (!\is_array($source)) {
            return;
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $categoryID) {
            $this->deleteCategory($categoryID);
            \executeHook(\HOOK_KATEGORIE_XML_BEARBEITEDELETES, ['kKategorie' => $categoryID]);
        }
    }

    /**
     * @param array $xml
     * @return int
     */
    private function handleInserts(array $xml): int
    {
        $category                 = new stdClass();
        $category->kKategorie     = 0;
        $category->kOberKategorie = 0;
        if (\is_array($xml['tkategorie attr'])) {
            $category->kKategorie     = (int)$xml['tkategorie attr']['kKategorie'];
            $category->kOberKategorie = (int)$xml['tkategorie attr']['kOberKategorie'];
        }
        if (!$category->kKategorie) {
            $this->logger->error('kKategorie fehlt! XML: ' . \print_r($xml, true));

            return 0;
        }
        if (!\is_array($xml['tkategorie'])) {
            return 0;
        }
        // Altes SEO merken => falls sich es bei der aktualisierten Kategorie ändert => Eintrag in tredirect
        $oldData = $this->db->queryPrepared(
            'SELECT cSeo, lft, rght, nLevel
                FROM tkategorie
                WHERE kKategorie = :categoryID',
            ['categoryID' => $category->kKategorie],
            ReturnType::SINGLE_OBJECT
        );
        $this->db->delete('tseo', ['kKey', 'cKey'], [$category->kKategorie, 'kKategorie']);
        $categories = $this->mapper->mapArray($xml, 'tkategorie', 'mKategorie');
        if ($categories[0]->kKategorie > 0) {
            if (!$categories[0]->cSeo) {
                $categories[0]->cSeo = Seo::getFlatSeoPath($categories[0]->cName);
            }
            $categories[0]->cSeo                  = Seo::checkSeo(Seo::getSeo($categories[0]->cSeo));
            $categories[0]->dLetzteAktualisierung = 'NOW()';
            $categories[0]->lft                   = $oldData->lft ?? 0;
            $categories[0]->rght                  = $oldData->rght ?? 0;
            $categories[0]->nLevel                = $oldData->nLevel ?? 0;
            $this->insertOnExistUpdate('tkategorie', $categories, ['kKategorie']);
            if (isset($oldData->cSeo)) {
                $this->checkDbeSXmlRedirect($oldData->cSeo, $categories[0]->cSeo);
            }
            $this->db->queryPrepared(
                "INSERT IGNORE INTO tseo
                    SELECT tkategorie.cSeo, 'kKategorie', tkategorie.kKategorie, tsprache.kSprache
                        FROM tkategorie, tsprache
                        WHERE tkategorie.kKategorie = :categoryID
                            AND tsprache.cStandard = 'Y'
                            AND tkategorie.cSeo != ''
                    ON DUPLICATE KEY UPDATE cSeo = (SELECT tkategorie.cSeo
                        FROM tkategorie, tsprache
                        WHERE tkategorie.kKategorie = :categoryID
                            AND tsprache.cStandard = 'Y'
                            AND tkategorie.cSeo != '')",
                ['categoryID' => (int)$categories[0]->kKategorie],
                ReturnType::DEFAULT
            );

            \executeHook(\HOOK_KATEGORIE_XML_BEARBEITEINSERT, ['oKategorie' => $categories[0]]);
        }
        $this->setLanguages($xml, $category->kKategorie, $categories[0]);
        $this->setCustomerGroups($xml, $category->kKategorie);
        $this->setCategoryDiscount($category->kKategorie);
        foreach ($this->getLinkedDiscountCategories($category->kKategorie) as $linkedCategory) {
            $this->setCategoryDiscount((int)$linkedCategory->kKategorie);
        }
        $this->setVisibility($xml, $category->kKategorie);
        $this->setAttributes($xml, $category->kKategorie);

        return $category->kKategorie;
    }

    /**
     * @param array  $xml
     * @param int    $categoryID
     * @param object $category
     */
    private function setLanguages(array $xml, int $categoryID, $category): void
    {
        $seoData      = $this->getSeoFromDB($categoryID, 'kKategorie', null, 'kSprache');
        $catLanguages = $this->mapper->mapArray($xml['tkategorie'], 'tkategoriesprache', 'mKategorieSprache');
        $langIDs      = [];
        $allLanguages = LanguageHelper::getAllLanguages(1);
        foreach ($catLanguages as $language) {
            // Sprachen die nicht im Shop vorhanden sind überspringen
            if (!LanguageHelper::isShopLanguage($language->kSprache, $allLanguages)) {
                continue;
            }
            if (!$language->cSeo) {
                $language->cSeo = $language->cName;
            }
            if (!$language->cSeo) {
                $language->cSeo = $category->cSeo;
            }
            if (!$language->cSeo) {
                $language->cSeo = $category->cName;
            }
            $language->cSeo = Seo::checkSeo(Seo::getSeo($language->cSeo));
            $this->insertOnExistUpdate('tkategoriesprache', [$language], ['kKategorie', 'kSprache']);

            $ins           = new stdClass();
            $ins->cSeo     = $language->cSeo;
            $ins->cKey     = 'kKategorie';
            $ins->kKey     = $language->kKategorie;
            $ins->kSprache = $language->kSprache;
            $this->db->upsert('tseo', $ins);
            if (isset($seoData[$language->kSprache])) {
                $this->checkDbeSXmlRedirect(
                    $seoData[$language->kSprache]->cSeo,
                    $language->cSeo
                );
            }
            $langIDs[] = (int)$language->kSprache;
        }
        $this->deleteByKey('tkategoriesprache', ['kKategorie' => $categoryID], 'kSprache', $langIDs);
    }

    /**
     * @param array $xml
     * @param int   $categoryID
     */
    private function setCustomerGroups(array $xml, int $categoryID): void
    {
        $pkValues = $this->insertOnExistsUpdateXMLinDB(
            $xml['tkategorie'],
            'tkategoriekundengruppe',
            'mKategorieKundengruppe',
            ['kKategorie', 'kKundengruppe']
        );
        $this->deleteByKey(
            'tkategoriekundengruppe',
            ['kKategorie' => $categoryID],
            'kKundengruppe',
            $pkValues['kKundengruppe']
        );
    }

    /**
     * @param array $xml
     * @param int   $categoryID
     */
    private function setAttributes(array $xml, int $categoryID): void
    {
        // Wawi sends category attributes in tkategorieattribut (function attributes)
        // and tattribut (localized attributes) nodes
        $pkValues   = $this->insertOnExistsUpdateXMLinDB(
            $xml['tkategorie'],
            'tkategorieattribut',
            'mKategorieAttribut',
            ['kKategorieAttribut']
        );
        $attributes = $this->mapper->mapArray($xml['tkategorie'], 'tattribut', 'mNormalKategorieAttribut');
        $attribPKs  = $pkValues['kKategorieAttribut'];
        if (\count($attributes) > 0) {
            $single = isset($xml['tkategorie']['tattribut attr']) && \is_array($xml['tkategorie']['tattribut attr']);
            $i      = 0;
            foreach ($attributes as $attribute) {
                $attribPKs[] = $this->saveAttribute(
                    $single ? $xml['tkategorie']['tattribut'] : $xml['tkategorie']['tattribut'][$i++],
                    $attribute
                );
            }
        }
        $this->db->queryPrepared(
            'DELETE tkategorieattribut, tkategorieattributsprache
                FROM tkategorieattribut
                LEFT JOIN tkategorieattributsprache
                    ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                WHERE tkategorieattribut.kKategorie = :categoryID' . (\count($attribPKs) > 0 ? '
                    AND tkategorieattribut.kKategorieAttribut NOT IN (' . \implode(', ', $attribPKs) . ')' : ''),
            ['categoryID' => $categoryID],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param array $xml
     * @param int   $categoryID
     */
    private function setVisibility(array $xml, int $categoryID): void
    {
        $pkValues = $this->insertOnExistsUpdateXMLinDB(
            $xml['tkategorie'],
            'tkategoriesichtbarkeit',
            'mKategorieSichtbarkeit',
            ['kKundengruppe', 'kKategorie']
        );
        $this->deleteByKey(
            'tkategoriesichtbarkeit',
            ['kKategorie' => $categoryID],
            'kKundengruppe',
            $pkValues['kKundengruppe']
        );
    }

    /**
     * @param int $id
     */
    private function deleteCategory(int $id): void
    {
        $this->db->queryPrepared(
            'DELETE tkategorieattribut, tkategorieattributsprache
                FROM tkategorieattribut
                LEFT JOIN tkategorieattributsprache
                    ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                WHERE tkategorieattribut.kKategorie = :categoryID',
            ['categoryID' => $id],
            ReturnType::DEFAULT
        );
        $this->db->delete('tseo', ['kKey', 'cKey'], [$id, 'kKategorie']);
        $this->db->delete('tkategorie', 'kKategorie', $id);
        $this->db->delete('tkategoriekundengruppe', 'kKategorie', $id);
        $this->db->delete('tkategoriesichtbarkeit', 'kKategorie', $id);
        $this->db->delete('tkategorieartikel', 'kKategorie', $id);
        $this->db->delete('tkategoriesprache', 'kKategorie', $id);
        $this->db->delete('tartikelkategorierabatt', 'kKategorie', $id);
    }

    /**
     * @param array  $xmlParent
     * @param object $attribute
     * @return int
     */
    private function saveAttribute($xmlParent, $attribute): int
    {
        // Fix: die Wawi überträgt für die normalen Attribute die ID in kAttribut statt in kKategorieAttribut
        if (!isset($attribute->kKategorieAttribut) && isset($attribute->kAttribut)) {
            $attribute->kKategorieAttribut = (int)$attribute->kAttribut;
            unset($attribute->kAttribut);
        }
        $this->insertOnExistUpdate('tkategorieattribut', [$attribute], ['kKategorieAttribut', 'kKategorie']);
        $localized = $this->mapper->mapArray($xmlParent, 'tattributsprache', 'mKategorieAttributSprache');
        // Die Standardsprache wird nicht separat übertragen und wird deshalb aus den Attributwerten gesetzt
        \array_unshift($localized, (object)[
            'kAttribut' => $attribute->kKategorieAttribut,
            'kSprache'  => $this->db->select('tsprache', 'cShopStandard', 'Y')->kSprache,
            'cName'     => $attribute->cName,
            'cWert'     => $attribute->cWert,
        ]);
        $this->upsert('tkategorieattributsprache', $localized, 'kAttribut', 'kSprache');
        $pkValues = $this->insertOnExistUpdate('tkategorieattributsprache', $localized, ['kAttribut', 'kSprache']);
        $this->deleteByKey(
            'tkategorieattributsprache',
            ['kAttribut' => $attribute->kKategorieAttribut],
            'kSprache',
            $pkValues['kSprache']
        );

        return (int)$attribute->kKategorieAttribut;
    }

    /**
     * @param int $categoryID
     */
    private function setCategoryDiscount(int $categoryID): void
    {
        $this->db->delete('tartikelkategorierabatt', 'kKategorie', $categoryID);
        $this->db->queryPrepared(
            'INSERT INTO tartikelkategorierabatt SELECT * FROM (
                SELECT tkategorieartikel.kArtikel, tkategoriekundengruppe.kKundengruppe, tkategorieartikel.kKategorie,
                       MAX(tkategoriekundengruppe.fRabatt) fRabatt
                FROM tkategoriekundengruppe
                INNER JOIN tkategorieartikel
                    ON tkategorieartikel.kKategorie = tkategoriekundengruppe.kKategorie
                LEFT JOIN tkategoriesichtbarkeit
                    ON tkategoriesichtbarkeit.kKategorie = tkategoriekundengruppe.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = tkategoriekundengruppe.kKundengruppe
                WHERE tkategoriekundengruppe.kKategorie = :categoryID
                    AND tkategoriesichtbarkeit.kKategorie IS NULL
                GROUP BY tkategorieartikel.kArtikel, tkategoriekundengruppe.kKundengruppe, tkategorieartikel.kKategorie
                HAVING MAX(tkategoriekundengruppe.fRabatt) > 0) AS tNew ON DUPLICATE KEY UPDATE
                    kKategorie = IF(tartikelkategorierabatt.fRabatt < tNew.fRabatt,
                        tNew.kKategorie,
                        tartikelkategorierabatt.kKategorie),
                    fRabatt    = IF(tartikelkategorierabatt.fRabatt < tNew.fRabatt,
                        tNew.fRabatt,
                        tartikelkategorierabatt.fRabatt)',
            ['categoryID' => $categoryID],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int $categoryID
     * @return array
     */
    private function getLinkedDiscountCategories(int $categoryID): array
    {
        return $this->db->queryPrepared(
            'SELECT DISTINCT tkgrp_b.kKategorie
                FROM tkategorieartikel tart_a
                INNER JOIN tkategorieartikel tart_b ON tart_a.kArtikel = tart_b.kArtikel
                    AND tart_a.kKategorie != tart_b.kKategorie
                INNER JOIN tkategoriekundengruppe tkgrp_b ON tart_b.kKategorie = tkgrp_b.kKategorie
                LEFT JOIN tkategoriekundengruppe tkgrp_a ON tkgrp_a.kKategorie = tart_a.kKategorie
                LEFT JOIN tkategoriesichtbarkeit tsicht ON tsicht.kKategorie = tkgrp_b.kKategorie
                    AND tsicht.kKundengruppe = tkgrp_b.kKundengruppe
                WHERE tart_a.kKategorie = :categoryID
                    AND tkgrp_b.fRabatt > COALESCE(tkgrp_a.fRabatt, 0)
                    AND tsicht.kKategorie IS NULL',
            [
                'categoryID' => $categoryID
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
}
