<?php

namespace JTL\Extensions\SelectionWizard;

use JTL\Catalog\Category\Kategorie;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Shop;
use stdClass;

/**
 * Class Location
 * @package JTL\Extensions\SelectionWizard
 */
class Location
{
    /**
     * @var int
     */
    public $kAuswahlAssistentOrt;

    /**
     * @var int
     */
    public $kAuswahlAssistentGruppe;

    /**
     * @var string
     */
    public $cKey;

    /**
     * @var int
     */
    public $kKey;

    /**
     * @var array
     */
    public $oOrt_arr;

    /**
     * @var string
     */
    public $cOrt;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Location constructor.
     * @param int  $locationID
     * @param int  $groupID
     * @param bool $backend
     */
    public function __construct(int $locationID = 0, int $groupID = 0, bool $backend = false)
    {
        $this->db = Shop::Container()->getDB();
        if ($locationID > 0 || $groupID > 0) {
            $this->loadFromDB($locationID, $groupID, $backend);
        }
    }

    /**
     * @param int  $locationID
     * @param int  $groupID
     * @param bool $backend
     */
    private function loadFromDB(int $locationID, int $groupID, bool $backend): void
    {
        if ($groupID > 0) {
            $this->oOrt_arr = [];
            $locationData   = $this->db->selectAll(
                'tauswahlassistentort',
                'kAuswahlAssistentGruppe',
                $groupID
            );
            foreach ($locationData as $loc) {
                $this->oOrt_arr[] = new self((int)$loc->kAuswahlAssistentOrt, 0, $backend);
            }

            return;
        }
        $location = $this->db->select(
            'tauswahlassistentort',
            'kAuswahlAssistentOrt',
            $locationID
        );
        if ($location === null) {
            return;
        }
        foreach (\array_keys(\get_object_vars($location)) as $member) {
            $this->$member = $location->$member;
        }
        $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
        $this->kAuswahlAssistentOrt    = (int)$this->kAuswahlAssistentOrt;
        $this->kKey                    = (int)$this->kKey;
        switch ($this->cKey) {
            case \AUSWAHLASSISTENT_ORT_KATEGORIE:
                if ($backend) {
                    unset($_SESSION['oKategorie_arr_new']);
                }
                $category = new Kategorie(
                    $this->kKey,
                    $this->getLanguage($this->kAuswahlAssistentGruppe)
                );

                $this->cOrt = $category->cName . ' (' . __('category') . ')';
                break;

            case \AUSWAHLASSISTENT_ORT_LINK:
                $language   = $this->db->select(
                    'tsprache',
                    'kSprache',
                    $this->getLanguage($this->kAuswahlAssistentGruppe)
                );
                $link       = $this->db->select(
                    'tlinksprache',
                    'kLink',
                    $this->kKey,
                    'cISOSprache',
                    $language->cISO,
                    null,
                    null,
                    false,
                    'cName'
                );
                $this->cOrt = isset($link->cName) ? ($link->cName . ' (CMS)') : null;
                break;

            case \AUSWAHLASSISTENT_ORT_STARTSEITE:
                $this->cOrt = 'Startseite';
                break;
        }
    }

    /**
     * @param int $groupID
     * @return int
     */
    private function getLanguage(int $groupID): int
    {
        $group = $this->db->queryPrepared(
            'SELECT kSprache
                FROM tauswahlassistentgruppe
                WHERE kAuswahlAssistentGruppe = :groupID',
            ['groupID' => $groupID],
            ReturnType::SINGLE_OBJECT
        );

        return (int)($group->kSprache ?? 0);
    }

    /**
     * @param array $params
     * @param int   $groupID
     * @return bool
     */
    public function saveLocation(array $params, int $groupID): bool
    {
        if ($groupID <= 0 || !\is_array($params) || \count($params) === 0) {
            return false;
        }
        if (isset($params['cKategorie']) && \mb_strlen($params['cKategorie']) > 0) {
            foreach (\explode(';', $params['cKategorie']) as $key) {
                $key = (int)$key;
                if ($key > 0 && \mb_strlen($key) > 0) {
                    $ins                          = new stdClass();
                    $ins->kAuswahlAssistentGruppe = $groupID;
                    $ins->cKey                    = \AUSWAHLASSISTENT_ORT_KATEGORIE;
                    $ins->kKey                    = $key;

                    $this->db->insert('tauswahlassistentort', $ins);
                }
            }
        }
        if (GeneralObject::hasCount('kLink_arr', $params)) {
            foreach ($params['kLink_arr'] as $key) {
                $key = (int)$key;
                if ($key > 0) {
                    $ins                          = new stdClass();
                    $ins->kAuswahlAssistentGruppe = $groupID;
                    $ins->cKey                    = \AUSWAHLASSISTENT_ORT_LINK;
                    $ins->kKey                    = $key;

                    $this->db->insert('tauswahlassistentort', $ins);
                }
            }
        }
        if (isset($params['nStartseite']) && (int)$params['nStartseite'] === 1) {
            $ins                          = new stdClass();
            $ins->kAuswahlAssistentGruppe = $groupID;
            $ins->cKey                    = \AUSWAHLASSISTENT_ORT_STARTSEITE;
            $ins->kKey                    = 1;

            $this->db->insert('tauswahlassistentort', $ins);
        }

        return false;
    }

    /**
     * @param array $params
     * @param int   $groupID
     * @return bool
     */
    public function updateLocation(array $params, int $groupID): bool
    {
        $rows = 0;
        if ($groupID > 0 && \is_array($params) && \count($params) > 0) {
            $rows = $this->db->delete(
                'tauswahlassistentort',
                'kAuswahlAssistentGruppe',
                $groupID
            );
        }

        return $rows > 0 && $this->saveLocation($params, $groupID);
    }

    /**
     * @param array $params
     * @param bool  $update
     * @return array
     */
    public function checkLocation(array $params, bool $update = false): array
    {
        $checks = [];
        // Ort
        if ((!isset($params['cKategorie']) || \mb_strlen($params['cKategorie']) === 0)
            && (!isset($params['kLink_arr'])
                || !\is_array($params['kLink_arr'])
                || \count($params['kLink_arr']) === 0)
            && (int)$params['nStartseite'] === 0
        ) {
            $checks['cOrt'] = 1;
        }
        $langID = (int)($params['kSprache'] ?? 0);
        // Ort Kategorie
        if (isset($params['cKategorie']) && \mb_strlen($params['cKategorie']) > 0) {
            $categories = \explode(';', $params['cKategorie']);
            if (!\is_array($categories) || \count($categories) === 0) {
                $checks['cKategorie'] = 1;
            }
            if (!\is_numeric($categories[0])) {
                $checks['cKategorie'] = 2;
            }
            foreach ($categories as $key) {
                $key = (int)$key;
                if ($key > 0 && \mb_strlen($key) > 0) {
                    if ($update) {
                        if ($this->isCategoryTaken(
                            $key,
                            $langID,
                            (int)$params['kAuswahlAssistentGruppe']
                        )) {
                            $checks['cKategorie'] = 3;
                        }
                    } elseif ($this->isCategoryTaken($key, $langID)) {
                        $checks['cKategorie'] = 3;
                    }
                }
            }
        }
        // Ort Spezialseite
        if (GeneralObject::hasCount('kLink_arr', $params)) {
            foreach ($params['kLink_arr'] as $key) {
                $key = (int)$key;
                if ($key <= 0) {
                    continue;
                }
                if ($update) {
                    if ($this->isLinkTaken(
                        $key,
                        $langID,
                        (int)$params['kAuswahlAssistentGruppe']
                    )) {
                        $checks['kLink_arr'] = 1;
                    }
                } elseif ($this->isLinkTaken($key, $langID)) {
                    $checks['kLink_arr'] = 1;
                }
            }
        }
        // Ort Startseite
        if (isset($params['nStartseite']) && (int)$params['nStartseite'] === 1) {
            if ($update) {
                if ($this->isStartPageTaken(
                    $langID,
                    (int)$params['kAuswahlAssistentGruppe']
                )) {
                    $checks['nStartseite'] = 1;
                }
            } elseif ($this->isStartPageTaken($langID)) {
                $checks['nStartseite'] = 1;
            }
        }

        return $checks;
    }

    /**
     * @param int $categoryID
     * @param int $languageID
     * @param int $groupID
     * @return bool
     */
    public function isCategoryTaken(int $categoryID, int $languageID, int $groupID = 0): bool
    {
        if ($categoryID === 0 || $languageID === 0) {
            return false;
        }
        $locationSQL = $groupID > 0
            ? ' AND o.kAuswahlAssistentGruppe != ' . $groupID
            : '';
        $item        = $this->db->queryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID' . $locationSQL . '
                    AND o.kKey = :catID',
            [
                'keyID'  => \AUSWAHLASSISTENT_ORT_KATEGORIE,
                'catID'  => $categoryID,
                'langID' => $languageID
            ],
            ReturnType::SINGLE_OBJECT
        );

        return ($item->kAuswahlAssistentOrt ?? 0) > 0;
    }

    /**
     * @param int $linkID
     * @param int $languageID
     * @param int $groupID
     * @return bool
     */
    public function isLinkTaken(int $linkID, int $languageID, int $groupID = 0): bool
    {
        if ($linkID === 0 || $languageID === 0) {
            return false;
        }
        $condSQL = $groupID > 0
            ? ' AND o.kAuswahlAssistentGruppe != ' . $groupID
            : '';
        $data    = $this->db->queryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID' . $condSQL . '
                    AND o.kKey = :linkID',
            [
                'langID' => $languageID,
                'keyID'  => \AUSWAHLASSISTENT_ORT_LINK,
                'linkID' => $linkID
            ],
            ReturnType::SINGLE_OBJECT
        );

        return ($data->kAuswahlAssistentOrt ?? 0) > 0;
    }

    /**
     * @param int $languageID
     * @param int $groupID
     * @return bool
     */
    public function isStartPageTaken(int $languageID, int $groupID = 0): bool
    {
        if ($languageID === 0) {
            return false;
        }
        $locationSQL = $groupID > 0
            ? ' AND o.kAuswahlAssistentGruppe != ' . $groupID
            : '';
        $item        = $this->db->queryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID' . $locationSQL . '
                    AND o.kKey = 1',
            ['langID' => $languageID, 'keyID' => \AUSWAHLASSISTENT_ORT_STARTSEITE],
            ReturnType::SINGLE_OBJECT
        );

        return ($item->kAuswahlAssistentOrt ?? 0) > 0;
    }

    /**
     * @param string $keyName
     * @param int    $id
     * @param int    $languageID
     * @param bool   $backend
     * @return Location|null
     */
    public function getLocation(string $keyName, int $id, int $languageID, bool $backend = false): ?self
    {
        $item = $this->db->executeQueryPrepared(
            'SELECT kAuswahlAssistentOrt
                FROM tauswahlassistentort AS o
                JOIN tauswahlassistentgruppe AS g
                    ON g.kAuswahlAssistentGruppe = o.kAuswahlAssistentGruppe
                    AND g.kSprache = :langID
                WHERE o.cKey = :keyID
                    AND o.kKey = :kkey',
            [
                'langID' => $languageID,
                'keyID'  => $keyName,
                'kkey'   => $id
            ],
            ReturnType::SINGLE_OBJECT
        );

        return isset($item->kAuswahlAssistentOrt) && $item->kAuswahlAssistentOrt > 0
            ? new self((int)$item->kAuswahlAssistentOrt, 0, $backend)
            : null;
    }
}
