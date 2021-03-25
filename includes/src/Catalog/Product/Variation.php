<?php declare(strict_types=1);

namespace JTL\Catalog\Product;

use stdClass;

/**
 * Class Variation
 * @package JTL\Catalog\Product
 */
class Variation
{
    /**
     * @var array
     */
    public $Werte = [];

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var string
     */
    public $cWaehlbar;

    /**
     * @var string
     */
    public $cTyp;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var int
     */
    public $nLieferbareVariationswerte = 0;

    /**
     * @param stdClass $data
     */
    public function init(stdClass $data): void
    {
        $this->kEigenschaft = (int)$data->kEigenschaft;
        $this->kArtikel     = (int)$data->kArtikel;
        $this->cWaehlbar    = $data->cWaehlbar;
        $this->cTyp         = $data->cTyp;
        $this->nSort        = (int)$data->nSort;
        $this->cName        = empty($data->cName_teigenschaftsprache)
            ? $data->cName
            : $data->cName_teigenschaftsprache;
        if ($data->cTyp === 'FREIFELD' || $data->cTyp === 'PFLICHT-FREIFELD') {
            $this->nLieferbareVariationswerte = 1;
        }
    }
}
