<?php declare(strict_types=1);

namespace JTL\Sitemap\Factories;

use Generator;
use JTL\DB\ReturnType;
use JTL\Sitemap\Items\NewsCategory as Item;
use PDO;
use function Functional\map;

/**
 * Class NewsCategory
 * @package JTL\Sitemap\Factories
 */
final class NewsCategory extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Generator
    {
        $languageIDs = map($languages, static function ($e) {
            return (int)$e->kSprache;
        });
        $res         = $this->db->query(
            "SELECT tnewskategorie.dLetzteAktualisierung AS dlm, tnewskategorie.kNewsKategorie, 
            tnewskategorie.cPreviewImage AS image, tseo.cSeo, tseo.kSprache AS langID
                FROM tnewskategorie
                JOIN tnewskategoriesprache t 
                    ON tnewskategorie.kNewsKategorie = t.kNewsKategorie
                JOIN tseo 
                    ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = t.languageID
                WHERE tnewskategorie.nAktiv = 1
                    AND tseo.kSprache IN (" . \implode(',', $languageIDs) . ')',
            ReturnType::QUERYSINGLE
        );
        while (($nc = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $item = new Item($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($nc, $languages);
            yield $item;
        }
    }
}
