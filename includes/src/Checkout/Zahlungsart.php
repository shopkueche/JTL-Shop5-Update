<?php

namespace JTL\Checkout;

use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\MainModel;
use JTL\Shop;

/**
 * Class Zahlungsart
 * @package JTL\Checkout
 */
class Zahlungsart extends MainModel
{
    /**
     * @var int
     */
    public $kZahlungsart;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cModulId;

    /**
     * @var string
     */
    public $cKundengruppen;

    /**
     * @var string
     */
    public $cZusatzschrittTemplate;

    /**
     * @var string
     */
    public $cPluginTemplate;

    /**
     * @var string
     */
    public $cBild;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var int
     */
    public $nMailSenden;

    /**
     * @var int
     */
    public $nActive;

    /**
     * @var string
     */
    public $cAnbieter;

    /**
     * @var string
     */
    public $cTSCode;

    /**
     * @var int
     */
    public $nWaehrendBestellung;

    /**
     * @var string
     */
    public $nCURL;

    /**
     * @var int
     */
    public $nSOAP;

    /**
     * @var int
     */
    public $nSOCKETS;

    /**
     * @var int
     */
    public $nNutzbar;

    /**
     * @var string
     */
    public $cHinweisText;

    /**
     * @var string
     */
    public $cHinweisTextShop;

    /**
     * @var string
     */
    public $cGebuehrname;

    /**
     * @var array
     */
    public $einstellungen;

    /**
     * @return int|null
     */
    public function getZahlungsart(): ?int
    {
        return $this->kZahlungsart;
    }

    /**
     * @param int $kZahlungsart
     * @return $this
     */
    public function setZahlungsart(int $kZahlungsart): self
    {
        $this->kZahlungsart = $kZahlungsart;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name): self
    {
        $this->cName = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getModulId(): ?string
    {
        return $this->cModulId;
    }

    /**
     * @param string $cModulId
     * @return $this
     */
    public function setModulId($cModulId): self
    {
        $this->cModulId = $cModulId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getKundengruppen(): ?string
    {
        return $this->cKundengruppen;
    }

    /**
     * @param string $cKundengruppen
     * @return $this
     */
    public function setKundengruppen($cKundengruppen): self
    {
        $this->cKundengruppen = $cKundengruppen;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getZusatzschrittTemplate(): ?string
    {
        return $this->cZusatzschrittTemplate;
    }

    /**
     * @param string $cZusatzschrittTemplate
     * @return $this
     */
    public function setZusatzschrittTemplate($cZusatzschrittTemplate): self
    {
        $this->cZusatzschrittTemplate = $cZusatzschrittTemplate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPluginTemplate(): ?string
    {
        return $this->cPluginTemplate;
    }

    /**
     * @param string $cPluginTemplate
     * @return $this
     */
    public function setPluginTemplate($cPluginTemplate): self
    {
        $this->cPluginTemplate = $cPluginTemplate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBild(): ?string
    {
        return $this->cBild;
    }

    /**
     * @param string $cBild
     * @return $this
     */
    public function setBild($cBild): self
    {
        $this->cBild = $cBild;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSort(): ?int
    {
        return $this->nSort;
    }

    /**
     * @param int $sort
     * @return $this
     */
    public function setSort(int $sort): self
    {
        $this->nSort = $sort;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMailSenden(): ?int
    {
        return $this->nMailSenden;
    }

    /**
     * @param int $nMailSenden
     * @return $this
     */
    public function setMailSenden(int $nMailSenden): self
    {
        $this->nMailSenden = $nMailSenden;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getActive(): ?int
    {
        return $this->nActive;
    }

    /**
     * @param int $nActive
     * @return $this
     */
    public function setActive(int $nActive): self
    {
        $this->nActive = $nActive;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAnbieter(): ?string
    {
        return $this->cAnbieter;
    }

    /**
     * @param string $cAnbieter
     * @return $this
     */
    public function setAnbieter($cAnbieter): self
    {
        $this->cAnbieter = $cAnbieter;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTSCode(): ?string
    {
        return $this->cTSCode;
    }

    /**
     * @param string $cTSCode
     * @return $this
     */
    public function setTSCode($cTSCode): self
    {
        $this->cTSCode = $cTSCode;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWaehrendBestellung(): ?int
    {
        return $this->nWaehrendBestellung;
    }

    /**
     * @param int $nWaehrendBestellung
     * @return $this
     */
    public function setWaehrendBestellung(int $nWaehrendBestellung): self
    {
        $this->nWaehrendBestellung = $nWaehrendBestellung;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCURL(): ?int
    {
        return $this->nCURL;
    }

    /**
     * @param int $nCURL
     * @return $this
     */
    public function setCURL($nCURL): self
    {
        $this->nCURL = (int)$nCURL;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSOAP(): ?int
    {
        return $this->nSOAP;
    }

    /**
     * @param int $nSOAP
     * @return $this
     */
    public function setSOAP($nSOAP): self
    {
        $this->nSOAP = (int)$nSOAP;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSOCKETS(): ?int
    {
        return $this->nSOCKETS;
    }

    /**
     * @param int $nSOCKETS
     * @return $this
     */
    public function setSOCKETS($nSOCKETS): self
    {
        $this->nSOCKETS = (int)$nSOCKETS;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNutzbar(): ?int
    {
        return $this->nNutzbar;
    }

    /**
     * @param int $nNutzbar
     * @return $this
     */
    public function setNutzbar($nNutzbar): self
    {
        $this->nNutzbar = (int)$nNutzbar;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHinweisText(): ?string
    {
        return $this->cHinweisText;
    }

    /**
     * @param string $cHinweisText
     * @return $this
     */
    public function setHinweisText($cHinweisText): self
    {
        $this->cHinweisText = $cHinweisText;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHinweisTextShop(): ?string
    {
        return $this->cHinweisTextShop;
    }

    /**
     * @param string $cHinweisTextShop
     * @return $this
     */
    public function setHinweisTextShop($cHinweisTextShop): self
    {
        $this->cHinweisTextShop = $cHinweisTextShop;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGebuehrname(): ?string
    {
        return $this->cGebuehrname;
    }

    /**
     * @param string $cGebuehrname
     * @return $this
     */
    public function setGebuehrname($cGebuehrname): self
    {
        $this->cGebuehrname = $cGebuehrname;

        return $this;
    }

    /**
     * @param int         $id
     * @param null|object $data
     * @param null|array  $option
     * @return $this
     */
    public function load($id, $data = null, $option = null): self
    {
        $id = (int)$id;
        if ($id <= 0) {
            return $this;
        }
        $iso  = $option['iso'] ?? Shop::getLanguageCode() ?? LanguageHelper::getDefaultLanguage()->getCode();
        $data = Shop::Container()->getDB()->queryPrepared(
            'SELECT z.kZahlungsart, COALESCE(s.cName, z.cName) AS cName, z.cModulId, z.cKundengruppen,
                    z.cZusatzschrittTemplate, z.cPluginTemplate, z.cBild, z.nSort, z.nMailSenden, z.nActive,
                    z.cAnbieter, z.cTSCode, z.nWaehrendBestellung, z.nCURL, z.nSOAP, z.nSOCKETS, z.nNutzbar,
                    s.cISOSprache, s.cGebuehrname, s.cHinweisText, s.cHinweisTextShop
                FROM tzahlungsart AS z
                LEFT JOIN tzahlungsartsprache AS s 
                    ON s.kZahlungsart = z.kZahlungsart
                    AND s.cISOSprache = :iso
                WHERE z.kZahlungsart = :pmID
                LIMIT 1',
            [
                'iso'  => $iso,
                'pmID' => $id
            ],
            ReturnType::SINGLE_OBJECT
        );
        if ($data !== false) {
            $this->loadObject($data);
        }

        return $this;
    }

    /**
     * @param bool        $active
     * @param string|null $iso
     * @return array
     */
    public static function loadAll(bool $active = true, string $iso = null): array
    {
        $payments = [];
        $where    = $active ? ' WHERE z.nActive = 1' : '';
        $iso      = $iso ?? Shop::getLanguageCode() ?? LanguageHelper::getDefaultLanguage()->getCode();
        $objs     = Shop::Container()->getDB()->queryPrepared(
            'SELECT *
                FROM tzahlungsart AS z
                LEFT JOIN tzahlungsartsprache AS s 
                    ON s.kZahlungsart = z.kZahlungsart
                    AND s.cISOSprache = :iso' . $where,
            ['iso' => $iso],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($objs as $obj) {
            $payments[] = new self(null, $obj);
        }

        return $payments;
    }
}
