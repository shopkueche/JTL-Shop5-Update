<?php

namespace JTL\Catalog;

use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Merkmal;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\map;
use function Functional\some;
use function Functional\sort;

/**
 * Class ComparisonList
 * @package JTL\Catalog
 */
class ComparisonList
{
    /**
     * @var array
     */
    public $oArtikel_arr = [];

    /**
     * ComparisonList constructor.
     * @param int   $productID
     * @param array $variations
     */
    public function __construct(int $productID = 0, array $variations = [])
    {
        if ($productID > 0) {
            $this->addProduct($productID, $variations);
        } else {
            $this->loadFromSession();
        }
    }

    /**
     * load comparelist from session
     */
    private function loadFromSession(): void
    {
        $compareList = Frontend::get('Vergleichsliste');
        if ($compareList !== null) {
            $defaultOptions = Artikel::getDefaultOptions();
            $baseURL        = Shop::Container()->getLinkService()->getStaticRoute('vergleichsliste.php');
            foreach ($compareList->oArtikel_arr as $item) {
                $product = new Artikel();
                $product->fuelleArtikel($item->kArtikel, $defaultOptions);
                $product->cURLDEL = $baseURL . '?vlplo=' . $item->kArtikel;
                if (isset($item->oVariationen_arr) && \count($item->oVariationen_arr) > 0) {
                    $product->Variationen = $item->oVariationen_arr;
                }
                $this->oArtikel_arr[] = $product;
            }
        }
    }

    /**
     * @return $this
     */
    public function umgebungsWechsel(): self
    {
        $defaultOptions = Artikel::getDefaultOptions();
        $compareList    = Frontend::get('Vergleichsliste');
        if ($compareList === null) {
            return $this;
        }
        foreach ($compareList->oArtikel_arr as $i => $item) {
            $product    = new stdClass();
            $tmpProduct = new Artikel();
            try {
                $tmpProduct->fuelleArtikel($item->kArtikel, $defaultOptions);
            } catch (Exception $e) {
                continue;
            }
            $product->kArtikel             = $item->kArtikel;
            $product->cName                = $tmpProduct->cName ?? '';
            $product->cURLFull             = $tmpProduct->cURLFull ?? '';
            $product->image                = $tmpProduct->Bilder[0] ?? '';
            $compareList->oArtikel_arr[$i] = $product;
        }

        return $this;
    }

    /**
     * @param int   $productID
     * @param array $variations
     * @return ComparisonList
     */
    public function addProduct(int $productID, array $variations = []): self
    {
        $product           = new stdClass();
        $tmpProduct        = (new Artikel())->fuelleArtikel($productID, Artikel::getDefaultOptions());
        $product->kArtikel = $productID;
        $product->cName    = $tmpProduct !== null ? $tmpProduct->cName : '';
        $product->cURLFull = $tmpProduct !== null ? $tmpProduct->cURLFull : '';
        $product->image    = $tmpProduct !== null ? $tmpProduct->Bilder[0] : '';
        if (\is_array($variations) && \count($variations) > 0) {
            $product->Variationen = $variations;
        }
        $this->oArtikel_arr[] = $product;

        Frontend::set('Vergleichsliste', $this);

        \executeHook(\HOOK_VERGLEICHSLISTE_CLASS_EINFUEGEN);

        return $this;
    }

    /**
     * @param int $productID
     * @return bool
     */
    public function productExists(int $productID): bool
    {
        return some($this->oArtikel_arr, static function ($e) use ($productID) {
            return (int)$e->kArtikel === $productID;
        });
    }

    /**
     * @return array
     * @former baueMerkmalundVariation()
     * @since 5.0.0
     */
    public function buildAttributeAndVariation(): array
    {
        $attributes = [];
        $variations = [];
        foreach ($this->oArtikel_arr as $product) {
            /** @var Artikel $product */
            if (\count($product->oMerkmale_arr) > 0) {
                // Falls das Merkmal Array nicht leer ist
                if (\count($attributes) > 0) {
                    foreach ($product->oMerkmale_arr as $oMerkmale) {
                        if (!$this->containsAttribute($attributes, $oMerkmale->kMerkmal)) {
                            $attributes[] = $oMerkmale;
                        }
                    }
                } else {
                    $attributes = $product->oMerkmale_arr;
                }
            }
            // Falls ein Artikel min. eine Variation enthält
            if (\count($product->Variationen) > 0) {
                if (\count($variations) > 0) {
                    foreach ($product->Variationen as $oVariationen) {
                        if (!$this->containsVariation($variations, $oVariationen->cName)) {
                            $variations[] = $oVariationen;
                        }
                    }
                } else {
                    $variations = $product->Variationen;
                }
            }
        }
        if (\count($attributes) > 0) {
            \uasort($attributes, static function (Merkmal $a, Merkmal $b) {
                return $a->nSort > $b->nSort;
            });
        }

        return [$attributes, $variations];
    }

    /**
     * @param array $attributes
     * @param int   $id
     * @return bool
     * @former istMerkmalEnthalten()
     * @since 5.0.0
     */
    public function containsAttribute(array $attributes, int $id): bool
    {
        return some($attributes, static function ($e) use ($id) {
            return (int)$e->kMerkmal === $id;
        });
    }

    /**
     * @param array  $variations
     * @param string $name
     * @return bool
     * @former istVariationEnthalten()
     * @since 5.0.0
     */
    public function containsVariation(array $variations, string $name): bool
    {
        return some($variations, static function ($e) use ($name) {
            return $e->cName === $name;
        });
    }

    /**
     * @param array $exclude
     * @param array $config
     * @return string
     * @since 5.0.0
     * @former gibMaxPrioSpalteV()
     */
    public function getMaxPrioCol(array $exclude, array $config): string
    {
        $max  = 0;
        $col  = '';
        $conf = $config['vergleichsliste'];
        if ($conf['vergleichsliste_artikelnummer'] > $max && !\in_array('cArtNr', $exclude, true)) {
            $max = $conf['vergleichsliste_artikelnummer'];
            $col = 'cArtNr';
        }
        if ($conf['vergleichsliste_hersteller'] > $max && !\in_array('cHersteller', $exclude, true)) {
            $max = $conf['vergleichsliste_hersteller'];
            $col = 'cHersteller';
        }
        if ($conf['vergleichsliste_beschreibung'] > $max && !\in_array('cBeschreibung', $exclude, true)) {
            $max = $conf['vergleichsliste_beschreibung'];
            $col = 'cBeschreibung';
        }
        if ($conf['vergleichsliste_kurzbeschreibung'] > $max && !\in_array('cKurzBeschreibung', $exclude, true)) {
            $max = $conf['vergleichsliste_kurzbeschreibung'];
            $col = 'cKurzBeschreibung';
        }
        if ($conf['vergleichsliste_artikelgewicht'] > $max && !\in_array('fArtikelgewicht', $exclude, true)) {
            $max = $conf['vergleichsliste_artikelgewicht'];
            $col = 'fArtikelgewicht';
        }
        if ($conf['vergleichsliste_versandgewicht'] > $max && !\in_array('fGewicht', $exclude, true)) {
            $max = $conf['vergleichsliste_versandgewicht'];
            $col = 'fGewicht';
        }
        if ($conf['vergleichsliste_merkmale'] > $max && !\in_array('Merkmale', $exclude, true)) {
            $max = $conf['vergleichsliste_merkmale'];
            $col = 'Merkmale';
        }
        if ($conf['vergleichsliste_variationen'] > $max && !\in_array('Variationen', $exclude, true)) {
            $col = 'Variationen';
        }

        return $col;
    }

    /**
     * @param bool $keysOnly
     * @param bool $newStandard
     * @return array
     */
    public function getPrioRows(bool $keysOnly = false, bool $newStandard = true): array
    {
        $conf = Shop::getSettings([\CONF_VERGLEICHSLISTE])['vergleichsliste'];
        $rows = [
            'vergleichsliste_artikelnummer',
            'vergleichsliste_hersteller',
            'vergleichsliste_beschreibung',
            'vergleichsliste_kurzbeschreibung',
            'vergleichsliste_artikelgewicht',
            'vergleichsliste_versandgewicht',
            'vergleichsliste_merkmale',
            'vergleichsliste_variationen'
        ];
        if ($newStandard) {
            $rows[] = 'vergleichsliste_verfuegbarkeit';
            $rows[] = 'vergleichsliste_lieferzeit';
        }
        $prioRows  = [];
        $ignoreRow = 0;
        foreach ($rows as $row) {
            if ($conf[$row] > $ignoreRow) {
                $prioRows[$row] = $this->getMappedRowNames($row, $conf);
            }
        }
        $prioRows = sort($prioRows, static function (array $left, array $right) {
            return $left['priority'] < $right['priority'];
        });

        return $keysOnly ? map($prioRows, static function (array $row) {
            return $row['key'];
        }) : $prioRows;
    }

    /**
     * @param string $confName
     * @param array  $conf
     * @return array
     */
    private function getMappedRowNames(string $confName, array $conf): array
    {
        switch ($confName) {
            case 'vergleichsliste_artikelnummer':
                return [
                    'key'      => 'cArtNr',
                    'name'     => Shop::Lang()->get('productNumber', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
            case 'vergleichsliste_hersteller':
                return [
                    'key'      => 'cHersteller',
                    'name'     => Shop::Lang()->get('manufacturer', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
            case 'vergleichsliste_beschreibung':
                return [
                    'key'      => 'cBeschreibung',
                    'name'     => Shop::Lang()->get('description', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
            case 'vergleichsliste_kurzbeschreibung':
                return [
                    'key'      => 'cKurzBeschreibung',
                    'name'     => Shop::Lang()->get('shortDescription', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
            case 'vergleichsliste_artikelgewicht':
                return [
                    'key'      => 'fArtikelgewicht',
                    'name'     => Shop::Lang()->get('productWeight', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
            case 'vergleichsliste_versandgewicht':
                return [
                    'key'      => 'fGewicht',
                    'name'     => Shop::Lang()->get('shippingWeight', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
            case 'vergleichsliste_merkmale':
                return [
                    'key'      => 'Merkmale',
                    'name'     => Shop::Lang()->get('characteristics', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
            case 'vergleichsliste_variationen':
                return [
                    'key'      => 'Variationen',
                    'name'     => Shop::Lang()->get('variations', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
            case 'vergleichsliste_verfuegbarkeit':
                return [
                    'key'      => 'verfuegbarkeit',
                    'name'     => Shop::Lang()->get('availability', 'productOverview'),
                    'priority' => $conf[$confName]
                ];
            case 'vergleichsliste_lieferzeit':
                return [
                    'key'      => 'lieferzeit',
                    'name'     => Shop::Lang()->get('shippingTime'),
                    'priority' => $conf[$confName]
                ];
            default:
                return [
                    'key'      => '',
                    'name'     => '',
                    'priority' => 0
                ];
        }
    }

    /**
     * Fügt nach jedem Preisvergleich eine Statistik in die Datenbank.
     * Es sind allerdings nur 3 Einträge pro IP und Tag möglich
     */
    public function save(): void
    {
        if (\count($this->oArtikel_arr) === 0) {
            return;
        }
        $db   = Shop::Container()->getDB();
        $data = $db->queryPrepared(
            'SELECT COUNT(kVergleichsliste) AS nVergleiche
                FROM tvergleichsliste
                WHERE cIP = :ip
                    AND dDate > DATE_SUB(NOW(),INTERVAL 1 DAY)',
            ['ip' => Request::getRealIP()],
            ReturnType::SINGLE_OBJECT
        );
        if ($data->nVergleiche < 3) {
            $ins        = new stdClass();
            $ins->cIP   = Request::getRealIP();
            $ins->dDate = \date('Y-m-d H:i:s');
            $id         = $db->insert('tvergleichsliste', $ins);
            foreach ($this->oArtikel_arr as $product) {
                $item                   = new stdClass();
                $item->kVergleichsliste = $id;
                $item->kArtikel         = $product->kArtikel;
                $item->cArtikelName     = $product->cName;
                $db->insert('tvergleichslistepos', $item);
            }
        }
    }
}
