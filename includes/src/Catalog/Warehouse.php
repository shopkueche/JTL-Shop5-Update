<?php

namespace JTL\Catalog;

use DateTime;
use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\MainModel;
use JTL\Shop;
use stdClass;

/**
 * Class Warehouse
 * @package JTL\Catalog
 */
class Warehouse extends MainModel
{
    /**
     * @var int
     */
    public $kWarenlager;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cKuerzel;

    /**
     * @var string
     */
    public $cLagerTyp;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cStrasse;

    /**
     * @var string
     */
    public $cPLZ;

    /**
     * @var string
     */
    public $cOrt;

    /**
     * @var string
     */
    public $cLand;

    /**
     * @var int
     */
    public $nFulfillment;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var stdClass
     */
    public $oLageranzeige;

    /**
     * @var array
     */
    public $cSpracheAssoc_arr;

    /**
     * @var float
     */
    public $fBestand;

    /**
     * @var float
     */
    public $fZulauf;

    /**
     * @var string
     */
    public $dZulaufDatum;

    /**
     * @var string
     */
    public $dZulaufDatum_de;

    /**
     * @return stdClass|null
     */
    public function getOLageranzeige(): ?stdClass
    {
        return $this->oLageranzeige;
    }

    /**
     * @param stdClass $oLageranzeige
     * @return $this
     */
    public function setOLageranzeige($oLageranzeige): self
    {
        $this->oLageranzeige = $oLageranzeige;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getLanguages(): ?array
    {
        return $this->cSpracheAssoc_arr;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setLanguages($languages): self
    {
        $this->cSpracheAssoc_arr = $languages;

        return $this;
    }

    /**
     * @return float
     */
    public function getStock()
    {
        return $this->fBestand;
    }

    /**
     * @param float $fBestand
     * @return $this
     */
    public function setStock($fBestand): self
    {
        $this->fBestand = $fBestand;

        return $this;
    }

    /**
     * @return float
     */
    public function getBackorder()
    {
        return $this->fZulauf;
    }

    /**
     * @param float $fZulauf
     * @return $this
     */
    public function setBackorder($fZulauf): self
    {
        $this->fZulauf = $fZulauf;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBackorderDate(): ?string
    {
        return $this->dZulaufDatum;
    }

    /**
     * @param string $dZulaufDatum
     * @return $this
     */
    public function setBackorderDate($dZulaufDatum): self
    {
        $this->dZulaufDatum = $dZulaufDatum;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBackorderDateDE(): ?string
    {
        return $this->dZulaufDatum_de;
    }

    /**
     * @param string $dZulaufDatum_de
     * @return $this
     */
    public function setBackorderDateDE($dZulaufDatum_de): self
    {
        $this->dZulaufDatum_de = $dZulaufDatum_de;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getID(): ?int
    {
        return $this->kWarenlager;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID(int $id): self
    {
        $this->kWarenlager = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWarenlager(): ?int
    {
        return $this->getID();
    }

    /**
     * @param int $kWarenlager
     * @return $this
     */
    public function setWarenlager(int $kWarenlager): self
    {
        return $this->setID($kWarenlager);
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
    public function getKuerzel(): ?string
    {
        return $this->cKuerzel;
    }

    /**
     * @param string $cKuerzel
     * @return $this
     */
    public function setKuerzel($cKuerzel): self
    {
        $this->cKuerzel = $cKuerzel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLagerTyp(): ?string
    {
        return $this->cLagerTyp;
    }

    /**
     * @param string $cLagerTyp
     * @return $this
     */
    public function setLagerTyp($cLagerTyp): self
    {
        $this->cLagerTyp = $cLagerTyp;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBeschreibung(): string
    {
        return $this->cBeschreibung;
    }

    /**
     * @param string $cBeschreibung
     * @return $this
     */
    public function setBeschreibung($cBeschreibung): self
    {
        $this->cBeschreibung = $cBeschreibung;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStrasse(): ?string
    {
        return $this->cStrasse;
    }

    /**
     * @param string $cStrasse
     * @return $this
     */
    public function setStrasse($cStrasse): self
    {
        $this->cStrasse = $cStrasse;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPLZ(): ?string
    {
        return $this->cPLZ;
    }

    /**
     * @param string $cPLZ
     * @return $this
     */
    public function setPLZ($cPLZ): self
    {
        $this->cPLZ = $cPLZ;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrt(): ?string
    {
        return $this->cOrt;
    }

    /**
     * @param string $cOrt
     * @return $this
     */
    public function setOrt($cOrt): self
    {
        $this->cOrt = $cOrt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLand(): ?string
    {
        return $this->cLand;
    }

    /**
     * @param string $cLand
     * @return $this
     */
    public function setLand($cLand): self
    {
        $this->cLand = $cLand;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFulfillment(): ?int
    {
        return $this->nFulfillment;
    }

    /**
     * @param int $nFulfillment
     * @return $this
     */
    public function setFulfillment($nFulfillment): self
    {
        $this->nFulfillment = (int)$nFulfillment;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAktiv(): ?int
    {
        return $this->nAktiv;
    }

    /**
     * @param int $nAktiv
     * @return $this
     */
    public function setAktiv($nAktiv): self
    {
        $this->nAktiv = (int)$nAktiv;

        return $this;
    }

    /**
     * @param int         $id
     * @param null|object $data
     * @param int|null    $option
     */
    public function load($id, $data = null, $option = null): void
    {
        if ($id !== null) {
            $id = (int)$id;
            if ($id > 0) {
                $select = '';
                $join   = '';
                if ($option !== null && (int)$option > 0) {
                    $option = (int)$option;
                    $select = ', IF (twarenlagersprache.cName IS NOT NULL, 
                    twarenlagersprache.cName, twarenlager.cName) AS cName';
                    $join   = ' LEFT JOIN twarenlagersprache 
                                    ON twarenlagersprache.kWarenlager = twarenlager.kWarenlager
                                    AND twarenlagersprache.kSprache = ' . $option;
                }

                $data = Shop::Container()->getDB()->query(
                    'SELECT twarenlager.* ' . $select . '
                         FROM twarenlager' . $join . '
                         WHERE twarenlager.kWarenlager = ' . $id,
                    ReturnType::SINGLE_OBJECT
                );
            }
        }
        if (isset($data->kWarenlager) && $data->kWarenlager > 0) {
            $this->loadObject($data);
        }
    }

    /**
     * @param bool $primary
     * @return bool|int
     * @throws Exception
     */
    public function save(bool $primary = true)
    {
        $data = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $cMember) {
            $data->$cMember = $this->$cMember;
        }
        if ($this->getWarenlager() === null) {
            $kPrim = Shop::Container()->getDB()->insert('twarenlager', $data);
            if ($kPrim > 0) {
                return $primary ? $kPrim : true;
            }
        } else {
            $xResult = $this->update();
            if ($xResult) {
                return $primary ? -1 : true;
            }
        }

        return false;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function update(): int
    {
        $db      = Shop::Container()->getDB();
        $sql     = 'UPDATE twarenlager SET ';
        $set     = [];
        $members = \array_keys(\get_object_vars($this));
        if (\is_array($members) && \count($members) > 0) {
            foreach ($members as $cMember) {
                $cMethod = 'get' . \mb_substr($cMember, 1);
                if (\method_exists($this, $cMethod)) {
                    $val    = $this->$cMethod();
                    $mValue = $val === null
                        ? 'NULL'
                        : ("'" . $db->escape($val) . "'");
                    $set[]  = "{$cMember} = {$mValue}";
                }
            }

            $sql .= \implode(', ', $set);
            $sql .= ' WHERE kWarenlager = ' . $this->kWarenlager;

            return $db->query($sql, ReturnType::AFFECTED_ROWS);
        }
        throw new Exception('ERROR: Object has no members!');
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->queryPrepared(
            'DELETE twarenlager, twarenlagersprache
                FROM twarenlager
                LEFT JOIN twarenlagersprache 
                    ON twarenlagersprache.kWarenlager = twarenlager.kWarenlager
                WHERE twarenlager.kWarenlager = :lid',
            ['lid' => (int)$this->kWarenlager],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @return bool
     */
    public function loadLanguages(): bool
    {
        if ($this->getWarenlager() > 0) {
            $data = Shop::Container()->getDB()->selectAll('twarenlagersprache', 'kWarenlager', $this->getWarenlager());
            if (\count($data) > 0) {
                $this->cSpracheAssoc_arr = [];
                foreach ($data as $item) {
                    $this->cSpracheAssoc_arr[(int)$item->kSprache] = $item->cName;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param bool $activeOnly
     * @param bool $loadLanguages
     * @return array
     */
    public static function getAll(bool $activeOnly = true, bool $loadLanguages = false): array
    {
        $warehouses = [];
        $sql        = $activeOnly ? ' WHERE nAktiv = 1' : '';
        $data       = Shop::Container()->getDB()->query(
            'SELECT *
               FROM twarenlager' . $sql,
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as $item) {
            $warehouse = new self(null, $item);
            if ($loadLanguages) {
                $warehouse->loadLanguages();
            }
            $warehouses[] = $warehouse;
        }

        return $warehouses;
    }

    /**
     * @param int        $productID
     * @param int|null   $langID
     * @param null|array $config
     * @param bool       $active
     * @return array
     */
    public static function getByProduct(
        int $productID,
        int $langID = null,
        $config = null,
        bool $active = true
    ): array {
        $warehouses = [];
        if ($productID > 0) {
            $sql  = $active ? ' AND twarenlager.nAktiv = 1' : '';
            $data = Shop::Container()->getDB()->queryPrepared(
                'SELECT tartikelwarenlager.*
                    FROM tartikelwarenlager
                    JOIN twarenlager 
                        ON twarenlager.kWarenlager = tartikelwarenlager.kWarenlager' . $sql . '
                    WHERE tartikelwarenlager.kArtikel = :articleID',
                ['articleID' => $productID],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($data as $item) {
                $warehouse               = new self($item->kWarenlager, null, $langID);
                $warehouse->fBestand     = $item->fBestand;
                $warehouse->fZulauf      = $item->fZulauf;
                $warehouse->dZulaufDatum = $item->dZulaufDatum;
                if ($warehouse->dZulaufDatum !== null && \mb_strlen($warehouse->dZulaufDatum) > 1) {
                    try {
                        $warehouse->dZulaufDatum_de = (new DateTime($item->dZulaufDatum))->format('d.m.Y');
                    } catch (Exception $exc) {
                        $warehouse->dZulaufDatum_de = '00.00.0000';
                    }
                }
                if (\is_array($config)) {
                    $warehouse->buildWarehouseInfo($warehouse->fBestand, $config);
                }
                $warehouses[] = $warehouse;
            }
        }

        return $warehouses;
    }

    /**
     * @param float $stock
     * @param array $config
     * @return $this
     */
    public function buildWarehouseInfo($stock, array $config): self
    {
        $this->oLageranzeige                = new stdClass();
        $this->oLageranzeige->cLagerhinweis = [];
        $conf                               = Shop::getSettings([\CONF_GLOBAL, \CONF_ARTIKELDETAILS]);
        if ($config['cLagerBeachten'] === 'Y') {
            if ($stock > 0) {
                $this->oLageranzeige->cLagerhinweis['genau']          = $stock . ' '
                    . (!empty($config['cEinheit']) ? ($config['cEinheit'] . ' ') : '')
                    . Shop::Lang()->get('inStock');
                $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productAvailable');
                if (isset($conf['artikeldetails']['artikel_lagerbestandsanzeige'])
                    && $conf['artikeldetails']['artikel_lagerbestandsanzeige'] === 'verfuegbarkeit'
                ) {
                    $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen');
                }
            } elseif ($config['cLagerKleinerNull'] === 'Y') {
                $this->oLageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen');
                $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen');
            } else {
                $this->oLageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('productNotAvailable');
                $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productNotAvailable');
            }
        } else {
            $this->oLageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen');
            $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen');
        }
        if ($config['cLagerBeachten'] === 'Y') {
            $this->oLageranzeige->nStatus   = 1;
            $this->oLageranzeige->AmpelText = $config['attribut_ampeltext_gelb'];
            if ($stock <= (int)$conf['global']['artikel_lagerampel_rot']) {
                $this->oLageranzeige->nStatus   = 0;
                $this->oLageranzeige->AmpelText = $config['attribut_ampeltext_rot'];
            }
            if ($config['cLagerBeachten'] !== 'Y'
                || $stock >= (int)$conf['global']['artikel_lagerampel_gruen']
                || ($config['cLagerBeachten'] === 'Y'
                    && $config['cLagerKleinerNull'] === 'Y'
                    && $conf['global']['artikel_ampel_lagernull_gruen'] === 'Y')
            ) {
                $this->oLageranzeige->nStatus   = 2;
                $this->oLageranzeige->AmpelText = $config['attribut_ampeltext_gruen'];
            }
        } else {
            $this->oLageranzeige->nStatus = (int)$conf['global']['artikel_lagerampel_keinlager'];

            switch ($this->oLageranzeige->nStatus) {
                case 1:
                    $this->oLageranzeige->AmpelText = $config['attribut_ampeltext_gelb'];
                    break;
                case 0:
                    $this->oLageranzeige->AmpelText = $config['attribut_ampeltext_rot'];
                    break;
                default:
                    $this->oLageranzeige->nStatus   = 2;
                    $this->oLageranzeige->AmpelText = $config['attribut_ampeltext_gruen'];
                    break;
            }
        }

        return $this;
    }

    /**
     * @param Artikel $item
     * @return string
     */
    public function getBackorderString(Artikel $item):string
    {
        $backorder = '';
        if ($item->cLagerBeachten === 'Y'
            && $this->getStock() <= 0
            && $this->getBackorder() > 0
            && $this->getBackorderDate() !== null
        ) {
            $backorder = \sprintf(
                Shop::Lang()->get('productInflowing', 'productDetails'),
                $this->getBackorder(),
                $item->cEinheit,
                $this->getBackorderDateDE()
            );
        }

        return $backorder;
    }
}
