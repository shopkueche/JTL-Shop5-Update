<?php

namespace JTL\Boxes;

use Exception;
use JTL\DB\ReturnType;
use JTL\Filter\ProductFilter;
use JTL\MagicCompatibilityTrait;
use JTL\Services\JTL\BoxService;
use JTL\Shop;
use SmartyException;
use stdClass;

/**
 * Class LegacyBoxes
 * @package JTL\Boxes
 * @deprecated since 5.0.0
 */
class LegacyBoxes
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'boxes'     => 'BoxList',
        'boxConfig' => 'Config'
    ];

    /**
     * @var array
     */
    public $boxConfig = [];

    /**
     * @var string
     */
    public $lagerFilter = '';

    /**
     * @var string
     */
    public $cVaterSQL = ' AND tartikel.kVaterArtikel = 0';

    /**
     * unrendered box template file name + data
     *
     * @var array
     */
    public $rawData = [];

    /**
     * @var array
     */
    public $visibility;

    /**
     * @var LegacyBoxes
     */
    private static $instance;

    /**
     * @var BoxService
     */
    private $boxService;

    /**
     * @return LegacyBoxes
     * @deprecated since 5.0.0
     */
    public static function getInstance(): self
    {
        \trigger_error(__CLASS__ . ' is deprecated.', \E_USER_DEPRECATED);

        return self::$instance ?? new self();
    }

    /**
     * @deprecated since 5.0.0
     */
    public function __construct()
    {
        $this->boxService = Shop::Container()->getBoxService();
        self::$instance   = $this;
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function getBoxList(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return $this->boxService->getBoxes();
    }

    public function setBoxList(): void
    {
        \trigger_error(__CLASS__ . ': setting boxes here is not possible anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function holeVorlagen(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return [];
    }

    /**
     * @return mixed
     * @deprecated since 5.0.0
     */
    public function gibBoxInhalt()
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return null;
    }

    /**
     * @param int  $page
     * @param bool $active
     * @return array
     * @deprecated since 5.0.0
     */
    public function holeBoxen(int $page = 0, bool $active = true): array
    {
        \trigger_error(
            __METHOD__ . ' is deprecated. Use ' . \get_class($this->boxService) . ' instead',
            \E_USER_DEPRECATED
        );
        if (\count($this->boxService->getBoxes()) === 0) {
            return $this->boxService->buildList($page, $active);
        }

        return $this->boxService->getBoxes();
    }

    /**
     * generate array of currently active boxes
     *
     * @param int  $page
     * @param bool $active
     * @return $this
     * @deprecated since 5.0.0
     */
    public function build(int $page = 0, bool $active = true): self
    {
        \trigger_error(
            __METHOD__ . ' is deprecated. Use ' . \get_class($this->boxService) . ' instead',
            \E_USER_DEPRECATED
        );
        if (\count($this->boxService->getBoxes()) === 0) {
            $this->boxService->buildList($page, $active);
        }

        return $this;
    }

    /**
     * supply data for specific box types
     *
     * @return mixed
     * @deprecated since 5.0.0
     */
    public function prepareBox()
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return null;
    }

    /**
     * @return array
     * @throws Exception
     * @throws SmartyException
     * @deprecated since 5.0.0
     */
    public function render(): array
    {
        \trigger_error(
            __METHOD__ . ' is deprecated. Use ' . \get_class($this->boxService) . ' instead',
            \E_USER_DEPRECATED
        );

        return $this->boxService->render($this->boxService->getBoxes(), Shop::getPageType());
    }

    /**
     * @deprecated since 5.0.0
     */
    public function addRecentlyViewed(): void
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 5.0.0
     * @return string
     */
    public function mappekSeite(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return 'Unbekannt';
    }

    /**
     * @return array|bool
     * @deprecated since 5.0.0
     */
    public function holeBoxAnzeige()
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzeBoxAnzeige(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @param int $kBoxvorlage
     * @return stdClass|null
     * @deprecated since 5.0.0
     */
    public function holeVorlage(int $kBoxvorlage): ?stdClass
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Shop::Container()->getDB()->select('tboxvorlage', 'kBoxvorlage', $kBoxvorlage);
    }

    /**
     * @deprecated since 5.0.0
     */
    public function holeContainer(): void
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzeBox(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @param int $id
     * @return stdClass
     * @deprecated since 5.0.0
     */
    public function holeBox(int $id): stdClass
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        $box = Shop::Container()->getDB()->query(
            'SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.cTitel, tboxen.ePosition,
                tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cVerfuegbar, tboxvorlage.cTemplate
                FROM tboxen
                LEFT JOIN tboxvorlage 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE kBox = ' . $id,
            ReturnType::SINGLE_OBJECT
        );

        $box->oSprache_arr      = [];
        $box->kBox              = (int)$box->kBox;
        $box->kBoxvorlage       = (int)$box->kBoxvorlage;
        $box->supportsRevisions = $box->kBoxvorlage === 30 || $box->kBoxvorlage === 31; // only "Eigene Box"

        return $box;
    }

    /**
     * @deprecated since 5.0.0
     */
    public function bearbeiteBox(): void
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 5.0.0
     */
    public function bearbeiteBoxSprache(): void
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 5.0.0
     */
    public function letzteSortierID(): void
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore..', \E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 5.0.0
     */
    public function filterBoxVisibility(): void
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore..', \E_USER_DEPRECATED);
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function sortBox(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function aktiviereBox(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function loescheBox(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return false;
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function gibLinkGruppen(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return Shop::Container()->getDB()->query('SELECT * FROM tlinkgruppe', ReturnType::ARRAY_OF_OBJECTS);
    }

    /**
     * @param int $kBoxvorlage
     * @return bool
     * @deprecated since 5.0.0
     */
    public function isVisible(int $kBoxvorlage): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        foreach ($this->boxes as $_position => $_boxes) {
            foreach ($_boxes as $_box) {
                if ((int)$_box->kBoxvorlage === $kBoxvorlage) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param ProductFilter $pf
     * @return bool
     * @deprecated since 5.0.0
     */
    public function gibBoxenFilterNach(ProductFilter $pf): bool
    {
        return $this->boxService->showBoxes($pf);
    }

    /**
     * get raw data from visible boxes
     * to allow custom renderes
     *
     * @return array
     * @deprecated since 5.0.0
     */
    public function getRawData(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return $this->boxService->getRawData();
    }

    /**
     * compatibility layer for gibBoxen() which returns unrendered content
     *
     * @return array
     * @deprecated since 5.0.0
     */
    public function compatGet(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return [];
    }

    /**
     * special json string for sidebar clouds
     *
     * @param array  $c
     * @param string $speed
     * @param string $opacity
     * @param bool   $color
     * @param bool   $hover
     * @return string
     * @deprecated since 5.0.0
     */
    public static function gibJSONString($c, $speed = '1', $opacity = '0.2', $color = false, $hover = false): string
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return '';
    }

    /**
     * get classname for sidebar panels
     *
     * @return string
     * @deprecated since 5.0.0
     */
    public function getClass(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return '';
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function getInvisibleBoxes(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', \E_USER_DEPRECATED);

        return [];
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function getValidPageTypes(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return [
            \PAGE_UNBEKANNT,
            \PAGE_ARTIKEL,
            \PAGE_ARTIKELLISTE,
            \PAGE_WARENKORB,
            \PAGE_MEINKONTO,
            \PAGE_KONTAKT,
            \PAGE_NEWS,
            \PAGE_NEWSLETTER,
            \PAGE_LOGIN,
            \PAGE_REGISTRIERUNG,
            \PAGE_BESTELLVORGANG,
            \PAGE_BEWERTUNG,
            \PAGE_PASSWORTVERGESSEN,
            \PAGE_WARTUNG,
            \PAGE_WUNSCHLISTE,
            \PAGE_VERGLEICHSLISTE,
            \PAGE_STARTSEITE,
            \PAGE_VERSAND,
            \PAGE_AGB,
            \PAGE_DATENSCHUTZ,
            \PAGE_LIVESUCHE,
            \PAGE_HERSTELLER,
            \PAGE_SITEMAP,
            \PAGE_GRATISGESCHENK,
            \PAGE_WRB,
            \PAGE_PLUGIN,
            \PAGE_NEWSLETTERARCHIV,
            \PAGE_EIGENE,
            \PAGE_AUSWAHLASSISTENT,
            \PAGE_BESTELLABSCHLUSS
        ];
    }
}
