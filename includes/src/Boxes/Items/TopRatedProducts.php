<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\Helpers\SearchSpecial;
use JTL\Shop;
use function Functional\map;

/**
 * Class TopRatedProducts
 * @package JTL\Boxes\Items
 */
final class TopRatedProducts extends AbstractBox
{
    /**
     * TopRatedProducts constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $products  = [];
        $parentSQL = ' AND tartikel.kVaterArtikel = 0';
        $cacheTags = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
        $limit     = (int)$config['boxen']['boxen_topbewertet_basisanzahl'];
        $cacheID   = 'bx_tprtd_' . $config['boxen']['boxen_topbewertet_minsterne'] . '_' .
            $limit . \md5($parentSQL);
        $cached    = true;
        if (($topRated = Shop::Container()->getCache()->get($cacheID)) === false) {
            $cached   = false;
            $topRated = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel, tartikelext.fDurchschnittsBewertung
                    FROM tartikel
                    JOIN tartikelext 
                        ON tartikel.kArtikel = tartikelext.kArtikel
                    WHERE ROUND(fDurchschnittsBewertung) >= ' . (int)$config['boxen']['boxen_topbewertet_minsterne'] .
                    ' ' . $parentSQL . ' ORDER BY tartikelext.fDurchschnittsBewertung DESC
                    LIMIT ' . $limit,
                ReturnType::ARRAY_OF_OBJECTS
            );
            Shop::Container()->getCache()->set($cacheID, $topRated, $cacheTags);
        }
        if (\count($topRated) > 0) {
            \shuffle($topRated);
            $res            = map(
                \array_slice($topRated, 0, $config['boxen']['boxen_topbewertet_anzahl']),
                static function ($productID) {
                    return (int)$productID->kArtikel;
                }
            );
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($res as $id) {
                $item = (new Artikel())->fuelleArtikel($id, $defaultOptions);
                if ($item !== null) {
                    $item->fDurchschnittsBewertung = \round($item->fDurchschnittsBewertung * 2) / 2;
                    $products[]                    = $item;
                }
            }
            $this->setShow(true);
            $this->setProducts($products);
            $this->setURL(SearchSpecial::buildURL(\SEARCHSPECIALS_TOPREVIEWS));

            \executeHook(\HOOK_BOXEN_INC_TOPBEWERTET, [
                'box'        => &$this,
                'cache_tags' => &$cacheTags,
                'cached'     => $cached
            ]);
        } else {
            $this->setShow(false);
        }
    }
}
