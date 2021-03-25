<?php

namespace JTL\Catalog\Product;

use JTL\DB\ReturnType;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\each;

/**
 * Class Bewertung
 * @package JTL\Catalog\Product
 */
class Bewertung
{
    /**
     * @var array
     */
    public $oBewertung_arr;

    /**
     * @var array
     */
    public $nSterne_arr;

    /**
     * @var int
     */
    public $nAnzahlSprache;

    /**
     * @var object
     */
    public $oBewertungGesamt;

    /**
     * @param int    $productID
     * @param int    $languageID
     * @param int    $pageOffset
     * @param int    $page
     * @param int    $stars
     * @param string $activate
     * @param int    $option
     * @param bool   $allLanguages
     */
    public function __construct(
        int $productID,
        int $languageID,
        int $pageOffset = -1,
        int $page = 1,
        int $stars = 0,
        string $activate = 'N',
        int $option = 0,
        bool $allLanguages = false
    ) {
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        if ($option === 1) {
            $this->holeHilfreichsteBewertung($productID, $languageID);
        } else {
            $this->holeProduktBewertungen(
                $productID,
                $languageID,
                $pageOffset,
                $page,
                $stars,
                $activate,
                $option,
                $allLanguages
            );
        }
    }

    /**
     * @param int $productID
     * @param int $languageID
     * @return $this
     */
    public function holeHilfreichsteBewertung(int $productID, int $languageID): self
    {
        $this->oBewertung_arr = [];
        if ($productID > 0 && $languageID > 0) {
            $data = Shop::Container()->getDB()->queryPrepared(
                "SELECT tbewertung.*,
                        DATE_FORMAT(dDatum, '%d.%m.%Y') AS Datum,
                        DATE_FORMAT(dAntwortDatum, '%d.%m.%Y') AS AntwortDatum,
                        tbewertunghilfreich.nBewertung AS rated
                    FROM tbewertung
                    LEFT JOIN tbewertunghilfreich
                      ON tbewertung.kBewertung = tbewertunghilfreich.kBewertung
                      AND tbewertunghilfreich.kKunde = :customerID
                    WHERE kSprache = " . $languageID . '
                        AND kArtikel = ' . $productID . '
                        AND nAktiv = 1
                    ORDER BY nHilfreich DESC
                    LIMIT 1',
                ['customerID' => Frontend::getCustomer()->getID()],
                ReturnType::SINGLE_OBJECT
            );
            if (!empty($data)) {
                $this->sanitizeRatingData($data);
                $data->nAnzahlHilfreich = $data->nHilfreich + $data->nNichtHilfreich;
            }

            \executeHook(\HOOK_BEWERTUNG_CLASS_HILFREICHSTEBEWERTUNG);
            $this->oBewertung_arr[] = $data;
        }

        return $this;
    }

    /**
     * @param stdClass $item
     */
    public function sanitizeRatingData(stdClass $item): void
    {
        $item->kBewertung      = (int)$item->kBewertung;
        $item->kArtikel        = (int)$item->kArtikel;
        $item->kKunde          = (int)$item->kKunde;
        $item->kSprache        = (int)$item->kSprache;
        $item->nHilfreich      = (int)$item->nHilfreich;
        $item->nNichtHilfreich = (int)$item->nNichtHilfreich;
        $item->nSterne         = (int)$item->nSterne;
        $item->nAktiv          = (int)$item->nAktiv;
    }

    /**
     * @param int $option
     * @return string
     */
    private function getOrderSQL(int $option): string
    {
        switch ($option) {
            case 3:
                return ' dDatum ASC';
            case 4:
                return ' nSterne DESC';
            case 5:
                return ' nSterne ASC';
            case 6:
                return ' nHilfreich DESC';
            case 7:
                return ' nHilfreich ASC';
            case 2:
            default:
                return ' dDatum DESC';
        }
    }

    /**
     * @param int    $productID
     * @param int    $languageID
     * @param int    $pageOffset
     * @param int    $page
     * @param int    $stars
     * @param string $activate
     * @param int    $option
     * @param bool   $allLanguages
     * @return $this
     */
    public function holeProduktBewertungen(
        int $productID,
        int $languageID,
        int $pageOffset,
        int $page = 1,
        int $stars = 0,
        string $activate = 'N',
        int $option = 0,
        bool $allLanguages = false
    ): self {
        $this->oBewertung_arr = [];
        if ($productID <= 0 || $languageID <= 0) {
            return $this;
        }
        $ratingCounts = [];
        $condSQL      = '';
        $orderSQL     = $this->getOrderSQL($option);
        $db           = Shop::Container()->getDB();
        \executeHook(\HOOK_BEWERTUNG_CLASS_SWITCH_SORTIERUNG);

        $activateSQL = $activate === 'Y'
            ? ' AND nAktiv = 1'
            : '';
        $langSQL     = $allLanguages ? '' : ' AND kSprache = ' . $languageID;
        // Anzahl Bewertungen für jeden Stern unabhängig von Sprache SHOP-2313
        if ($stars !== -1) {
            if ($stars > 0) {
                $condSQL = ' AND nSterne = ' . $stars;
            }
            $ratingCounts = $db->query(
                'SELECT COUNT(*) AS nAnzahl, nSterne
                    FROM tbewertung
                    WHERE kArtikel = ' . $productID . $activateSQL . '
                    GROUP BY nSterne
                    ORDER BY nSterne DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        if ($page > 0) {
            $limitSQL = '';
            if ($pageOffset > 0) {
                $limitSQL = ($page > 1)
                    ? ' LIMIT ' . (($page - 1) * $pageOffset) . ', ' . $pageOffset
                    : ' LIMIT ' . $pageOffset;
            }
            $this->oBewertung_arr = $db->queryPrepared(
                "SELECT tbewertung.*,
                        DATE_FORMAT(dDatum, '%d.%m.%Y') AS Datum,
                        DATE_FORMAT(dAntwortDatum, '%d.%m.%Y') AS AntwortDatum,
                        tbewertunghilfreich.nBewertung AS rated
                    FROM tbewertung
                    LEFT JOIN tbewertunghilfreich
                      ON tbewertung.kBewertung = tbewertunghilfreich.kBewertung
                      AND tbewertunghilfreich.kKunde = :customerID
                    WHERE kArtikel = " . $productID . $langSQL . $condSQL . $activateSQL . '
                    ORDER BY' . $orderSQL . $limitSQL,
                ['customerID' => Frontend::getCustomer()->getID()],
                ReturnType::ARRAY_OF_OBJECTS
            );
            each($this->oBewertung_arr, [$this, 'sanitizeRatingData']);
        }
        $total = $db->query(
            'SELECT COUNT(*) AS nAnzahl, tartikelext.fDurchschnittsBewertung AS fDurchschnitt
                FROM tartikelext
                JOIN tbewertung 
                    ON tbewertung.kArtikel = tartikelext.kArtikel
                WHERE tartikelext.kArtikel = ' . $productID . $activateSQL . '
                GROUP BY tartikelext.kArtikel',
            ReturnType::SINGLE_OBJECT
        );
        // Anzahl Bewertungen für aktuelle Sprache
        $totalLocalized = $db->query(
            'SELECT COUNT(*) AS nAnzahlSprache
                FROM tbewertung
                WHERE kArtikel = ' . $productID . $langSQL . $activateSQL,
            ReturnType::SINGLE_OBJECT
        );
        if (isset($total->fDurchschnitt) && (int)$total->fDurchschnitt > 0) {
            $total->fDurchschnitt   = \round($total->fDurchschnitt * 2) / 2;
            $total->nAnzahl         = (int)$total->nAnzahl;
            $this->oBewertungGesamt = $total;
        } else {
            $total                  = new stdClass();
            $total->fDurchschnitt   = 0;
            $total->nAnzahl         = 0;
            $this->oBewertungGesamt = $total;
        }
        $this->nAnzahlSprache = ((int)$totalLocalized->nAnzahlSprache > 0)
            ? (int)$totalLocalized->nAnzahlSprache
            : 0;
        foreach ($this->oBewertung_arr as $i => $rating) {
            $this->oBewertung_arr[$i]->nAnzahlHilfreich = $rating->nHilfreich + $rating->nNichtHilfreich;
        }
        $this->nSterne_arr = [0, 0, 0, 0, 0];
        foreach ($ratingCounts as $item) {
            $this->nSterne_arr[5 - (int)$item->nSterne] = (int)$item->nAnzahl;
        }
        \executeHook(\HOOK_BEWERTUNG_CLASS_BEWERTUNG, ['oBewertung' => &$this]);

        return $this;
    }
}
