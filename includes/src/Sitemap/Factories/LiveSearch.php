<?php declare(strict_types=1);

namespace JTL\Sitemap\Factories;

use Generator;
use JTL\DB\ReturnType;
use JTL\Sitemap\Items\LiveSearch as Item;
use PDO;
use function Functional\map;

/**
 * Class LiveSearch
 * @package JTL\Sitemap\Factories
 */
final class LiveSearch extends AbstractFactory
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
            "SELECT tsuchanfrage.kSuchanfrage, tseo.cSeo, tsuchanfrage.dZuletztGesucht AS dlm,
            tseo.kSprache AS langID
                FROM tsuchanfrage
                JOIN tseo 
                    ON tseo.cKey = 'kSuchanfrage'
                    AND tseo.kKey = tsuchanfrage.kSuchanfrage
                WHERE tsuchanfrage.nAktiv = 1
                    AND tsuchanfrage.kSprache IN (" . \implode(',', $languageIDs) . ')
                ORDER BY tsuchanfrage.kSuchanfrage',
            ReturnType::QUERYSINGLE
        );
        while (($ls = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $item = new Item($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($ls, $languages);
            yield $item;
        }
    }
}
