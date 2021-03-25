<?php declare(strict_types=1);

namespace JTL\Sitemap\Factories;

use Generator;
use JTL\DB\ReturnType;
use JTL\Sitemap\Items\Manufacturer as Item;
use PDO;
use function Functional\map;

/**
 * Class Manufacturer
 * @package JTL\Sitemap\Factories
 */
final class Manufacturer extends AbstractFactory
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
            "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cBildpfad AS image, 
            tseo.cSeo, tseo.kSprache AS langID
                FROM thersteller
                JOIN tseo 
                    ON tseo.cKey = 'kHersteller'
                    AND tseo.kKey = thersteller.kHersteller
                    AND tseo.kSprache IN (" . \implode(',', $languageIDs) . ')
                ORDER BY thersteller.kHersteller',
            ReturnType::QUERYSINGLE
        );
        while (($mf = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $item = new Item($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($mf, $languages);
            yield $item;
        }
    }
}
