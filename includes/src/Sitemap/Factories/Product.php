<?php declare(strict_types=1);

namespace JTL\Sitemap\Factories;

use Generator;
use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Sitemap\Items\Product as Item;
use PDO;
use function Functional\first;
use function Functional\map;

/**
 * Class Product
 * @package JTL\Sitemap\Factories
 */
final class Product extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Generator
    {
        $defaultCustomerGroupID  = first($customerGroups);
        $defaultLang             = LanguageHelper::getDefaultLanguage();
        $defaultLangID           = (int)$defaultLang->kSprache;
        $_SESSION['kSprache']    = $defaultLangID;
        $_SESSION['cISOSprache'] = $defaultLang->cISO;
        $andWhere                = '';
        $filterConf              = (int)$this->config['global']['artikel_artikelanzeigefilter'];

        $languageIDs = map($languages, static function ($e) {
            return (int)$e->kSprache;
        });
        if ($this->config['sitemap']['sitemap_varkombi_children_export'] !== 'Y') {
            $andWhere .= ' AND tartikel.kVaterArtikel = 0';
        }
        if ($filterConf === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            $andWhere .= " AND (tartikel.cLagerBeachten = 'N' OR tartikel.fLagerbestand > 0)";
        } elseif ($filterConf === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            $andWhere .= " AND (tartikel.cLagerBeachten = 'N' 
                               OR tartikel.cLagerKleinerNull = 'Y' 
                               OR tartikel.fLagerbestand > 0)";
        }
        $res = $this->db->queryPrepared(
            "SELECT tartikel.kArtikel, tartikel.dLetzteAktualisierung AS dlm, 
            tseo.cSeo, tseo.kSprache AS langID
                FROM tartikel
                JOIN tseo 
                    ON tseo.cKey = 'kArtikel'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache IN (" . \implode(',', $languageIDs) . ')
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = :cgid
                WHERE tartikelsichtbarkeit.kArtikel IS NULL' . $andWhere . '
                ORDER BY tartikel.kArtikel',
            ['cgid' => $defaultCustomerGroupID],
            ReturnType::QUERYSINGLE
        );

        while (($product = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $item = new Item($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($product, $languages);
            yield $item;
        }
    }
}
