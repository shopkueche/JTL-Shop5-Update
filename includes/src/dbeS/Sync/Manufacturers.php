<?php

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Helpers\Seo;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use stdClass;
use function Functional\flatten;

/**
 * Class Manufacturers
 * @package JTL\dbeS\Sync
 */
final class Manufacturers extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        $cacheTags = [];
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'del_hersteller.xml') !== false) {
                $cacheTags[] = $this->handleDeletes($xml);
            } elseif (\strpos($file, 'hersteller.xml') !== false) {
                $cacheTags[] = $this->handleInserts($xml);
            }
        }
        $this->cache->flushTags(\array_unique(flatten($cacheTags)));

        return null;
    }

    /**
     * @param array $xml
     * @return array
     */
    private function handleDeletes(array $xml): array
    {
        $cacheTags = [];
        $source    = $xml['del_hersteller']['kHersteller'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $manufacturerID) {
            $affectedProducts = $this->db->selectAll(
                'tartikel',
                'kHersteller',
                $manufacturerID,
                'kArtikel'
            );
            $this->db->delete('tseo', ['kKey', 'cKey'], [$manufacturerID, 'kHersteller']);
            $this->db->delete('thersteller', 'kHersteller', $manufacturerID);
            $this->db->delete('therstellersprache', 'kHersteller', $manufacturerID);

            \executeHook(\HOOK_HERSTELLER_XML_BEARBEITEDELETES, ['kHersteller' => $manufacturerID]);
            $cacheTags[] = \CACHING_GROUP_MANUFACTURER . '_' . $manufacturerID;
            foreach ($affectedProducts as $product) {
                $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . $product->kArtikel;
            }
        }

        return flatten($cacheTags);
    }

    /**
     * @param array $xml
     * @return array
     */
    private function handleInserts(array $xml): array
    {
        $source = $xml['hersteller']['thersteller'] ?? null;
        if (!\is_array($source)) {
            return [];
        }
        $languages     = LanguageHelper::getAllLanguages();
        $manufacturers = $this->mapper->mapArray($xml['hersteller'], 'thersteller', 'mHersteller');
        $mfCount       = \count($manufacturers);
        $cacheTags     = [];
        for ($i = 0; $i < $mfCount; $i++) {
            $id               = (int)$manufacturers[$i]->kHersteller;
            $affectedProducts = $this->db->selectAll('tartikel', 'kHersteller', $id, 'kArtikel');
            if (!\trim($manufacturers[$i]->cSeo)) {
                $manufacturers[$i]->cSeo = Seo::getFlatSeoPath($manufacturers[$i]->cName);
            }
            // alten Bildpfad merken
            $manufacturerImage            = $this->db->query(
                'SELECT cBildPfad 
                FROM thersteller 
                WHERE kHersteller = ' . $id,
                ReturnType::SINGLE_OBJECT
            );
            $manufacturers[$i]->cBildPfad = $manufacturerImage->cBildPfad ?? '';
            $manufacturers[$i]->cSeo      = Seo::getSeo($manufacturers[$i]->cSeo);
            $this->upsert('thersteller', [$manufacturers[$i]], 'kHersteller');

            $xmlLanguage = [];
            if (isset($source[$i])) {
                $xmlLanguage = $source[$i];
            } elseif (isset($source['therstellersprache'])) {
                $xmlLanguage = $source;
            }
            $newSeo = $this->updateSeo($id, $languages, $xmlLanguage, $manufacturers[$i]->cSeo);
            if ($newSeo !== $manufacturers[$i]->cSeo) {
                $this->db->update('thersteller', 'kHersteller', $id, (object)[
                    'cSeo' => $newSeo,
                ]);
            }
            $this->db->delete('therstellersprache', 'kHersteller', $id);

            $this->upsertXML(
                $xmlLanguage,
                'therstellersprache',
                'mHerstellerSprache',
                'kHersteller',
                'kSprache'
            );

            \executeHook(\HOOK_HERSTELLER_XML_BEARBEITEINSERT, ['oHersteller' => $manufacturers[$i]]);
            $cacheTags[] = \CACHING_GROUP_MANUFACTURER . '_' . $id;
            foreach ($affectedProducts as $product) {
                $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . (int)$product->kArtikel;
            }
        }

        return $cacheTags;
    }

    /**
     * @param int             $id
     * @param LanguageModel[] $languages
     * @param array           $xmlLanguage
     * @param string          $slug
     * @return string
     */
    private function updateSeo(int $id, array $languages, array $xmlLanguage, $slug): string
    {
        $this->db->delete('tseo', ['kKey', 'cKey'], [$id, 'kHersteller']);
        $mfSeo  = $this->mapper->mapArray($xmlLanguage, 'therstellersprache', 'mHerstellerSpracheSeo');
        $result = $slug;
        foreach ($languages as $language) {
            $baseSeo = $slug;
            foreach ($mfSeo as $mf) {
                if (isset($mf->kSprache) && !empty($mf->cSeo) && (int)$mf->kSprache === $language->getId()) {
                    $baseSeo = Seo::getSeo($mf->cSeo);
                    break;
                }
            }
            $seo           = new stdClass();
            $seo->cSeo     = Seo::checkSeo($baseSeo);
            $seo->cKey     = 'kHersteller';
            $seo->kKey     = $id;
            $seo->kSprache = $language->getId();
            $this->db->insert('tseo', $seo);

            if ($language->default === 'Y') {
                $result = $seo->cSeo;
            }
        }

        return $result;
    }
}
