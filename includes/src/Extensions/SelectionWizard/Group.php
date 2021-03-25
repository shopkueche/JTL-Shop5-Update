<?php

namespace JTL\Extensions\SelectionWizard;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;
use stdClass;

/**
 * Class Group
 * @package JTL\Extensions\SelectionWizard
 */
class Group
{
    /**
     * @var int
     */
    public $kAuswahlAssistentGruppe;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var array
     */
    public $oAuswahlAssistentFrage_arr;

    /**
     * @var array
     */
    public $oAuswahlAssistentOrt_arr;

    /**
     * @var string
     */
    public $cSprache;

    /**
     * @var int
     */
    public $nStartseite;

    /**
     * @var string
     */
    public $cKategorie;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Group constructor.
     * @param int  $groupID
     * @param bool $active
     * @param bool $activeOnly
     * @param bool $backend
     */
    public function __construct(int $groupID = 0, bool $active = true, bool $activeOnly = true, bool $backend = false)
    {
        $this->db = Shop::Container()->getDB();
        if ($groupID > 0) {
            $this->loadFromDB($groupID, $active, $activeOnly, $backend);
        }
    }

    /**
     * @param int  $groupID
     * @param bool $active
     * @param bool $activeOnly
     * @param bool $backend
     */
    private function loadFromDB(int $groupID, bool $active, bool $activeOnly, bool $backend): void
    {
        $activeSQL = $active ? ' AND nAktiv = 1' : '';
        $group     = $this->db->queryPrepared(
            'SELECT *
                FROM tauswahlassistentgruppe
                WHERE kAuswahlAssistentGruppe = :groupID' .
            $activeSQL,
            ['groupID' => $groupID],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($group->kAuswahlAssistentGruppe) && $group->kAuswahlAssistentGruppe > 0) {
            $question = new Question();
            foreach (\array_keys(\get_object_vars($group)) as $member) {
                $this->$member = $group->$member;
            }
            $this->kAuswahlAssistentGruppe    = (int)$this->kAuswahlAssistentGruppe;
            $this->kSprache                   = (int)$this->kSprache;
            $this->nAktiv                     = (int)$this->nAktiv;
            $this->oAuswahlAssistentFrage_arr = $question->getQuestions($groupID, $activeOnly);
            $location                         = new Location(0, $groupID, $backend);
            $this->oAuswahlAssistentOrt_arr   = $location->oOrt_arr;
            foreach ($this->oAuswahlAssistentOrt_arr as $location) {
                if ($location->cKey === \AUSWAHLASSISTENT_ORT_KATEGORIE) {
                    $this->cKategorie .= $location->kKey . ';';
                }
                if ($location->cKey === \AUSWAHLASSISTENT_ORT_STARTSEITE) {
                    $this->nStartseite = 1;
                }
            }
            $language       = $this->db->queryPrepared(
                'SELECT cNameDeutsch 
                    FROM tsprache 
                    WHERE kSprache = :langID',
                ['langID' => (int)$this->kSprache],
                ReturnType::SINGLE_OBJECT
            );
            $this->cSprache = $language->cNameDeutsch;
        }
    }

    /**
     * @param int  $langID
     * @param bool $active
     * @param bool $activeOnly
     * @param bool $backend
     * @return array
     */
    public function getGroups(int $langID, bool $active = true, bool $activeOnly = true, bool $backend = false): array
    {
        $groups    = [];
        $activeSQL = $active ? ' AND nAktiv = 1' : '';
        $groupData = $this->db->queryPrepared(
            'SELECT kAuswahlAssistentGruppe AS id
                FROM tauswahlassistentgruppe
                WHERE kSprache = :langID' . $activeSQL,
            ['langID' => $langID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($groupData as $item) {
            $groups[] = new self((int)$item->id, $active, $activeOnly, $backend);
        }

        return $groups;
    }

    /**
     * @param array $params
     * @param bool  $primary
     * @return array|bool
     */
    public function saveGroup(array $params, bool $primary = false)
    {
        $checks = $this->checkGroup($params);
        if (\count($checks) !== 0) {
            return $checks;
        }
        $this->nAktiv                  = (int)$this->nAktiv;
        $this->kSprache                = (int)$this->kSprache;
        $this->nStartseite             = (int)$this->nStartseite;
        $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;

        $data                = new stdClass();
        $data->kSprache      = $this->kSprache;
        $data->cName         = $this->cName;
        $data->cBeschreibung = $this->cBeschreibung;
        $data->nAktiv        = $this->nAktiv;

        $groupID = $this->db->insert('tauswahlassistentgruppe', $data);
        if ($groupID > 0) {
            $location = new Location();
            $location->saveLocation($params, $groupID);

            return $primary ? $groupID : true;
        }

        return false;
    }

    /**
     * @param array $params
     * @return array|bool
     */
    public function updateGroup(array $params)
    {
        $validation = $this->checkGroup($params, true);
        if (\count($validation) !== 0) {
            return $validation;
        }
        $upd                = new stdClass();
        $upd->kSprache      = $this->kSprache;
        $upd->cName         = $this->cName;
        $upd->cBeschreibung = $this->cBeschreibung;
        $upd->nAktiv        = $this->nAktiv;

        $this->db->update(
            'tauswahlassistentgruppe',
            'kAuswahlAssistentGruppe',
            (int)$this->kAuswahlAssistentGruppe,
            $upd
        );
        $location = new Location();
        $location->updateLocation($params, $this->kAuswahlAssistentGruppe);

        return true;
    }

    /**
     * @param array $params
     * @param bool  $update
     * @return array
     */
    public function checkGroup(array $params, bool $update = false): array
    {
        $validation = [];
        if (empty($this->cName)) {
            $validation['cName'] = 1;
        }
        if ($this->kSprache === 0) {
            $validation['kSprache'] = 1;
        }
        if ($this->nAktiv !== 0 && $this->nAktiv !== 1) {
            $validation['nAktiv'] = 1;
        }
        $location = (new Location())->checkLocation($params, $update);

        return \array_merge($location, $validation);
    }

    /**
     * @param array $groupIDs
     * @return bool
     */
    public function deleteGroup(array $groupIDs): bool
    {
        foreach (\array_map('\intval', $groupIDs) as $groupID) {
            $this->db->queryPrepared(
                'DELETE tag, taf, tao
                    FROM tauswahlassistentgruppe tag
                    LEFT JOIN tauswahlassistentfrage taf
                        ON taf.kAuswahlAssistentGruppe = tag.kAuswahlAssistentGruppe
                    LEFT JOIN tauswahlassistentort tao
                        ON tao.kAuswahlAssistentGruppe = tag.kAuswahlAssistentGruppe
                    WHERE tag.kAuswahlAssistentGruppe = :groupID',
                ['groupID' => (int)$groupID],
                ReturnType::AFFECTED_ROWS
            );
        }

        return true;
    }
}
