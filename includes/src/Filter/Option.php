<?php declare(strict_types=1);

namespace JTL\Filter;

use JTL\Media\MultiSizeImage;

/**
 * Class Option
 *
 * @package JTL\Filter
 *
 * @property $kHersteller
 * @property $nAnzahlTagging
 * @property $kKategorie
 * @property $nVon
 * @property $cVonLocalized
 * @property $nBis
 * @property $cBisLocalized
 * @property $nAnzahlArtikel
 * @property $nStern
 * @property $kKey
 * @property $cSuche
 * @property $kSuchanfrage
 */
class Option extends AbstractFilter
{
    use MultiSizeImage;

    /**
     * @var string
     */
    private $param = '';

    /**
     * @var string
     */
    private $url;

    /**
     * if set to true, ProductFilterURL::getURL() will not return a SEO URL
     *
     * @var bool
     */
    private $disableSeoURLs = false;

    /**
     * @var string
     */
    private $class = '';

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var int
     */
    public $nAktiv = 0;

    /**
     * @var array
     */
    public static $mapping = [
        'cName'          => 'Name',
        'nAnzahl'        => 'Count',
        'nAnzahlArtikel' => 'Count',
        'cURL'           => 'URL',
        'Klasse'         => 'Class',
        'nSortNr'        => 'Sort',
        'kSuchanfrage'   => 'Value',
        'kTag'           => 'Value',
        'kKey'           => 'Value',
        'kKategorie'     => 'Value',
        'kMerkmal'       => 'Value',
        'nSterne'        => 'Value',
    ];

    /**
     * Option constructor.
     * @param null $productFilter
     */
    public function __construct($productFilter = null)
    {
        parent::__construct($productFilter);
        $this->isInitialized = true;
        $this->options       = [];
    }

    /**
     * @param string $value
     * @return string|null
     */
    private static function getMapping($value): ?string
    {
        return self::$mapping[$value] ?? null;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function setIsActive(bool $active): FilterInterface
    {
        $this->isActive = $active;
        $this->nAktiv   = (int)$active;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass(string $class): FilterInterface
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getParam(): string
    {
        return $this->param;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setParam($param): FilterInterface
    {
        $this->param = $param;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return $this
     */
    public function setURL($url): FilterInterface
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDisableSeoURLs(): bool
    {
        return $this->disableSeoURLs;
    }

    /**
     * @param bool $disableSeoURLs
     * @return $this
     */
    public function setDisableSeoURLs($disableSeoURLs): FilterInterface
    {
        $this->disableSeoURLs = $disableSeoURLs;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptions($mixed = null): array
    {
        return $this->options;
    }

    /**
     * @param Option $option
     * @return $this
     */
    public function addOption($option): FilterInterface
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function setData($name, $value): FilterInterface
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getData($name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function __set($name, $value)
    {
        if (($mapped = self::getMapping($name)) !== null) {
            $method = 'set' . $mapped;

            return $this->$method($value);
        }

        $this->data[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (($mapped = self::getMapping($name)) !== null) {
            $method = 'get' . $mapped;

            return $this->$method();
        }

        return $this->data[$name] ?? null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return \property_exists($this, $name) || self::getMapping($name) !== null || isset($this->data[$name]);
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [];
    }
}
