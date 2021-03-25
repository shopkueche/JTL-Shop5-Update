<?php

namespace JTL;

use JTL\Catalog\Product\Artikel;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use stdClass;

/**
 * Class ImageMap
 * @package JTL
 */
class ImageMap implements IExtensionPoint
{
    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * ImageMap constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db            = $db;
        $this->kSprache      = Shop::getLanguageID();
        $this->kKundengruppe = isset($_SESSION['Kundengruppe']->kKundengruppe)
            ? Session\Frontend::getCustomerGroup()->getID()
            : null;
        if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
            $this->kKundengruppe = (int)$_SESSION['Kunde']->kKundengruppe;
        }
    }

    /**
     * @param int  $id
     * @param bool $fetchAll
     * @return $this
     */
    public function init($id, $fetchAll = false): self
    {
        $imageMap = $this->fetch($id, $fetchAll);
        if (\is_object($imageMap)) {
            Shop::Smarty()->assign('oImageMap', $imageMap);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function fetchAll(): array
    {
        return $this->db->query(
            'SELECT *, IF(
                (CURDATE() >= DATE(vDatum)) AND (
                    bDatum IS NULL 
                    OR CURDATE() <= DATE(bDatum)
                    OR bDatum = 0), 1, 0) AS active 
                FROM timagemap
                ORDER BY cTitel ASC',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param int  $id
     * @param bool $fetchAll
     * @param bool $fill
     * @return stdClass|bool
     */
    public function fetch(int $id, bool $fetchAll = false, bool $fill = true)
    {
        $sql = 'SELECT *
                    FROM timagemap
                    WHERE kImageMap = ' . $id;
        if (!$fetchAll) {
            $sql .= ' AND (CURDATE() >= DATE(vDatum)) AND (bDatum IS NULL OR CURDATE() <= DATE(bDatum) OR bDatum = 0)';
        }
        $imageMap = $this->db->query($sql, ReturnType::SINGLE_OBJECT);
        if (!\is_object($imageMap)) {
            return false;
        }
        $imageMap->oArea_arr = $this->db->selectAll(
            'timagemaparea',
            'kImageMap',
            (int)$imageMap->kImageMap
        );
        $imageMap->cBildPfad = Shop::getImageBaseURL() . \PFAD_IMAGEMAP . $imageMap->cBildPfad;
        $parsed              = \parse_url($imageMap->cBildPfad);
        $imageMap->cBild     = \mb_substr($parsed['path'], \mb_strrpos($parsed['path'], '/') + 1);
        $defaultOptions      = Artikel::getDefaultOptions();
        if (!\file_exists(\PFAD_ROOT . \PFAD_IMAGEMAP . $imageMap->cBild)) {
            return $imageMap;
        }
        [$imageMap->fWidth, $imageMap->fHeight] = \getimagesize(\PFAD_ROOT . \PFAD_IMAGEMAP . $imageMap->cBild);
        foreach ($imageMap->oArea_arr as $area) {
            $area->kImageMapArea = (int)$area->kImageMapArea;
            $area->kImageMap     = (int)$area->kImageMap;
            $area->kArtikel      = (int)$area->kArtikel;
            $area->oCoords       = new stdClass();
            $aMap                = \explode(',', $area->cCoords);
            if (\count($aMap) === 4) {
                $area->oCoords->x = (int)$aMap[0];
                $area->oCoords->y = (int)$aMap[1];
                $area->oCoords->w = (int)$aMap[2];
                $area->oCoords->h = (int)$aMap[3];
            }

            $area->oArtikel = null;
            if ($area->kArtikel > 0) {
                $area->oArtikel = new Artikel();
                if ($fill === true) {
                    $area->oArtikel->fuelleArtikel(
                        $area->kArtikel,
                        $defaultOptions,
                        $this->kKundengruppe ?? 0,
                        $this->kSprache
                    );
                } else {
                    $area->oArtikel->kArtikel = $area->kArtikel;
                    $area->oArtikel->cName    = $this->db->select(
                        'tartikel',
                        'kArtikel',
                        $area->kArtikel,
                        null,
                        null,
                        null,
                        null,
                        false,
                        'cName'
                    )->cName;
                }
                if (\mb_strlen($area->cTitel) === 0) {
                    $area->cTitel = $area->oArtikel->cName;
                }
                if (\mb_strlen($area->cUrl) === 0) {
                    $area->cUrl = $area->oArtikel->cURL;
                }
                if (\mb_strlen($area->cBeschreibung) === 0) {
                    $area->cBeschreibung = $area->oArtikel->cKurzBeschreibung;
                }
            }
        }

        return $imageMap;
    }

    /**
     * @param string $title
     * @param string $imagePath
     * @param string $dateFrom
     * @param string $dateUntil
     * @return int
     */
    public function save($title, $imagePath, $dateFrom, $dateUntil): int
    {
        $ins            = new stdClass();
        $ins->cTitel    = $title;
        $ins->cBildPfad = $imagePath;
        $ins->vDatum    = $dateFrom ?? 'NOW()';
        $ins->bDatum    = $dateUntil ?? '_DBNULL_';

        return $this->db->insert('timagemap', $ins);
    }

    /**
     * @param int    $id
     * @param string $title
     * @param string $imagePath
     * @param string $dateFrom
     * @param string $dateUntil
     * @return bool
     */
    public function update(int $id, $title, $imagePath, $dateFrom, $dateUntil): bool
    {
        if (empty($dateFrom)) {
            $dateFrom = 'NOW()';
        }
        if (empty($dateUntil)) {
            $dateUntil = '_DBNULL_';
        }
        $upd            = new stdClass();
        $upd->cTitel    = $title;
        $upd->cBildPfad = $imagePath;
        $upd->vDatum    = $dateFrom;
        $upd->bDatum    = $dateUntil;

        return $this->db->update('timagemap', 'kImageMap', $id, $upd) >= 0;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->db->delete('timagemap', 'kImageMap', $id) >= 0;
    }

    /**
     * @param stdClass $data
     */
    public function saveAreas($data): void
    {
        $this->db->delete('timagemaparea', 'kImageMap', (int)$data->kImageMap);
        foreach ($data->oArea_arr as $area) {
            $ins                = new stdClass();
            $ins->kImageMap     = $area->kImageMap;
            $ins->kArtikel      = $area->kArtikel;
            $ins->cStyle        = $area->cStyle;
            $ins->cTitel        = $area->cTitel;
            $ins->cUrl          = $area->cUrl;
            $ins->cBeschreibung = $area->cBeschreibung;
            $ins->cCoords       = $area->oCoords->x . ',' .
                $area->oCoords->y . ',' .
                $area->oCoords->w . ',' .
                $area->oCoords->h;

            $this->db->insert('timagemaparea', $ins);
        }
    }
}
