<?php declare(strict_types=1);

namespace JTL\Catalog\Product;

use JTL\Catalog\Currency;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use stdClass;

/**
 * Class VariationValue
 * @package JTL\Catalog\Product
 */
class VariationValue
{
    use MultiSizeImage;

    /**
     * @var int
     */
    public $kEigenschaftWert;

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var float
     */
    public $fAufpreisNetto;

    /**
     * @var float
     */
    public $fGewichtDiff;

    /**
     * @var string
     */
    public $cArtNr;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var float
     */
    public $fLagerbestand;

    /**
     * @var float
     */
    public $fPackeinheit;

    /**
     * @var bool
     */
    public $inStock;

    /**
     * @var bool
     */
    public $notExists;

    /**
     * @var stdClass
     */
    public $oVariationsKombi;

    /**
     * @var string
     */
    public $cAufpreisLocalized;

    /**
     * @var array
     */
    public $cPreisVPEWertAufpreis = [];

    /**
     * @var array
     */
    public $cPreisVPEWertInklAufpreis = [];

    /**
     * @var string
     */
    public $cPreisInklAufpreis;

    /**
     * @var float
     */
    public $fAufpreis;

    /**
     * @var float
     */
    public $fVPEWert;

    /**
     * @var string
     */
    public $cPfad;

    /**
     * @var string
     */
    public $cBildPfad;

    /**
     * @var string
     */
    public $cBildPfadFull;

    /**
     * @var string
     */
    public $cBildPfadMini;

    /**
     * @var string
     */
    public $cBildPfadMiniFull;

    /**
     * @var string
     */
    public $cBildPfadGross;

    /**
     * @var string
     */
    public $cBildPfadGrossFull;

    /**
     * @var string
     */
    public $cPfadMini;

    /**
     * @var string
     */
    public $cPfadMiniFull;

    /**
     * @var string
     */
    public $cPfadKlein;

    /**
     * @var string
     */
    public $cPfadKleinFull;

    /**
     * @var string
     */
    public $cPfadNormal;

    /**
     * @var string
     */
    public $cPfadNormalFull;

    /**
     * @var string
     */
    public $cPfadGross;

    /**
     * @var string
     */
    public $cPfadGrossFull;

    /**
     * VariationValue constructor.
     */
    public function __construct()
    {
        $this->setImageType(Image::TYPE_VARIATION);
    }

    /**
     * @param stdClass $data
     * @param int      $cntVariationen
     * @param          $tmpDiscount
     */
    public function init(stdClass $data, int $cntVariationen, $tmpDiscount): void
    {
        $this->kEigenschaftWert = (int)$data->kEigenschaftWert;
        $this->kEigenschaft     = (int)$data->kEigenschaft;
        if (!empty($data->localizedName)) {
            $this->cName = $data->localizedName;
        } else {
            $this->cName = Text::htmlentitiesOnce(
                $data->cName_teigenschaftwert ?? '',
                \ENT_COMPAT | \ENT_HTML401
            );
        }
        $this->fAufpreisNetto = $data->fAufpreisNetto;
        $this->fGewichtDiff   = $data->fGewichtDiff;
        $this->cArtNr         = $data->cArtNr;
        $this->nSort          = $data->teigenschaftwert_nSort;
        $this->fLagerbestand  = $data->fLagerbestand;
        $this->fPackeinheit   = $data->fPackeinheit;
        $this->inStock        = true;
        $this->notExists      = isset($data->nMatched)
            && (int)$data->nMatched < $cntVariationen - 1;

        if (isset($data->fVPEWert) && $data->fVPEWert > 0) {
            $this->fVPEWert = $data->fVPEWert;
        }
        if ($data->fAufpreisNetto_teigenschaftwertaufpreis !== null) {
            $this->fAufpreisNetto =
                $data->fAufpreisNetto_teigenschaftwertaufpreis * ((100 - $tmpDiscount) / 100);
        }
        if ((int)$this->fPackeinheit === 0) {
            $this->fPackeinheit = 1;
        }
    }

    /**
     * @param stdClass $data
     * @param Artikel  $product
     */
    public function addChildItems(stdClass $data, Artikel $product): void
    {
        $varCombi                         = new stdClass();
        $varCombi->kArtikel               = $data->tartikel_kArtikel ?? null;
        $varCombi->tartikel_fLagerbestand = $data->tartikel_fLagerbestand ?? null;
        $varCombi->cLagerBeachten         = $data->cLagerBeachten ?? null;
        $varCombi->cLagerKleinerNull      = $data->cLagerKleinerNull ?? null;
        $varCombi->cLagerVariation        = $data->cLagerVariation ?? null;

        if ($product->nIstVater === 1 && isset($data->cMergedLagerBeachten)) {
            $varCombi->tartikel_fLagerbestand = $data->fMergedLagerbestand ?? null;
            $varCombi->cLagerBeachten         = $data->cMergedLagerBeachten ?? null;
            $varCombi->cLagerKleinerNull      = $data->cMergedLagerKleinerNull ?? null;
            $varCombi->cLagerVariation        = $data->cMergedLagerVariation ?? null;
        }

        $stockInfo = $product->getStockInfo((object)[
            'cLagerVariation'   => $varCombi->cLagerVariation,
            'fLagerbestand'     => $varCombi->tartikel_fLagerbestand,
            'cLagerBeachten'    => $varCombi->cLagerBeachten,
            'cLagerKleinerNull' => $varCombi->cLagerKleinerNull,
        ]);

        $this->inStock          = $stockInfo->inStock;
        $this->notExists        = $this->notExists || $stockInfo->notExists;
        $this->oVariationsKombi = $varCombi;
    }

    /**
     * @param string $path
     * @param string $imageBaseURL
     * @return bool
     */
    public function addImages(string $path, string $imageBaseURL): bool
    {
        if (!$path || !\file_exists(\PFAD_ROOT . \PFAD_VARIATIONSBILDER_NORMAL . $path)) {
            return false;
        }
        $this->cPfad = $path;
        $this->generateAllImageSizes(true, 1, $path);

        $this->cBildPfadMini  = \PFAD_VARIATIONSBILDER_MINI . $path;
        $this->cBildPfad      = \PFAD_VARIATIONSBILDER_NORMAL . $path;
        $this->cBildPfadGross = \PFAD_VARIATIONSBILDER_GROSS . $path;

        $this->cBildPfadMiniFull  = $imageBaseURL . \PFAD_VARIATIONSBILDER_MINI . $path;
        $this->cBildPfadFull      = $imageBaseURL . \PFAD_VARIATIONSBILDER_NORMAL . $path;
        $this->cBildPfadGrossFull = $imageBaseURL . \PFAD_VARIATIONSBILDER_GROSS . $path;

        // compatibility
        $this->cPfadMini   = \PFAD_VARIATIONSBILDER_MINI . $path;
        $this->cPfadKlein  = \PFAD_VARIATIONSBILDER_NORMAL . $path;
        $this->cPfadNormal = \PFAD_VARIATIONSBILDER_NORMAL . $path;
        $this->cPfadGross  = \PFAD_VARIATIONSBILDER_GROSS . $path;

        $this->cPfadMiniFull   = $imageBaseURL . \PFAD_VARIATIONSBILDER_MINI . $path;
        $this->cPfadKleinFull  = $imageBaseURL . \PFAD_VARIATIONSBILDER_NORMAL . $path;
        $this->cPfadNormalFull = $imageBaseURL . \PFAD_VARIATIONSBILDER_NORMAL . $path;
        $this->cPfadGrossFull  = $imageBaseURL . \PFAD_VARIATIONSBILDER_GROSS . $path;

        return true;
    }

    /**
     * @param Artikel   $product
     * @param int|float $taxRate
     * @param Currency  $currency
     * @param bool|int  $mayViewPrices
     * @param int       $precision
     * @param string    $per
     */
    public function addPrices(Artikel $product, $taxRate, Currency $currency, $mayViewPrices, $precision, $per): void
    {
        if ($mayViewPrices && isset($this->fVPEWert) && $this->fVPEWert > 0) {
            $base                           = $this->fAufpreisNetto / $this->fVPEWert;
            $this->cPreisVPEWertAufpreis[0] = Preise::getLocalizedPriceString(
                Tax::getGross($base, $taxRate),
                $currency,
                true,
                $precision
            ) . $per;

            $this->cPreisVPEWertAufpreis[1] = Preise::getLocalizedPriceString(
                $base,
                $currency,
                true,
                $precision
            ) . $per;

            $base = ($this->fAufpreisNetto + $product->Preise->fVKNetto) / $this->fVPEWert;

            $this->cPreisVPEWertInklAufpreis[0] = Preise::getLocalizedPriceString(
                Tax::getGross($base, $taxRate),
                $currency,
                true,
                $precision
            ) . $per;
            $this->cPreisVPEWertInklAufpreis[1] = Preise::getLocalizedPriceString(
                $base,
                $currency,
                true,
                $precision
            ) . $per;
        }
        if (isset($this->fAufpreisNetto) && $this->fAufpreisNetto != 0) {
            $surcharge                   = $this->fAufpreisNetto;
            $this->cAufpreisLocalized[0] = Preise::getLocalizedPriceString(
                Tax::getGross($surcharge, $taxRate, 4),
                $currency
            );
            $this->cAufpreisLocalized[1] = Preise::getLocalizedPriceString($surcharge, $currency);
            // Wenn der Artikel ein VarikombiKind ist, rechne nicht nochmal die Variationsaufpreise drauf
            if ($product->kVaterArtikel > 0) {
                $this->cPreisInklAufpreis[0] = Preise::getLocalizedPriceString(
                    Tax::getGross($product->Preise->fVKNetto, $taxRate),
                    $currency
                );
                $this->cPreisInklAufpreis[1] = Preise::getLocalizedPriceString($product->Preise->fVKNetto, $currency);
            } else {
                $this->cPreisInklAufpreis[0] = Preise::getLocalizedPriceString(
                    Tax::getGross($surcharge + $product->Preise->fVKNetto, $taxRate),
                    $currency
                );
                $this->cPreisInklAufpreis[1] = Preise::getLocalizedPriceString(
                    $surcharge + $product->Preise->fVKNetto,
                    $currency
                );
            }

            if ($this->fAufpreisNetto > 0) {
                $this->cAufpreisLocalized[0] = '+ ' . $this->cAufpreisLocalized[0];
                $this->cAufpreisLocalized[1] = '+ ' . $this->cAufpreisLocalized[1];
            } else {
                $this->cAufpreisLocalized[0] = \str_replace('-', '- ', $this->cAufpreisLocalized[0]);
                $this->cAufpreisLocalized[1] = \str_replace('-', '- ', $this->cAufpreisLocalized[1]);
            }
            $surcharge = $this->fAufpreisNetto;

            $this->fAufpreis[0] = Tax::getGross($surcharge * $currency->getConversionFactor(), $taxRate);
            $this->fAufpreis[1] = $surcharge * $currency->getConversionFactor();

            if ($surcharge > 0) {
                $product->nVariationsAufpreisVorhanden = 1;
            }
        }
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->kEigenschaftWert;
    }
}
