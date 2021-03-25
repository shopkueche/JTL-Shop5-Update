<?php

namespace JTL\Extensions\SelectionWizard;

use JTL\Catalog\Product\Merkmal;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;
use stdClass;

/**
 * Class Question
 * @package JTL\Extensions\SelectionWizard
 */
class Question
{
    /**
     * @var int
     */
    public $kAuswahlAssistentFrage = 0;

    /**
     * @var int
     */
    public $kAuswahlAssistentGruppe = 0;

    /**
     * @var int
     */
    public $kMerkmal = 0;

    /**
     * @var string
     */
    public $cFrage = '';

    /**
     * @var int
     */
    public $nSort = 0;

    /**
     * @var int
     */
    public $nAktiv = 0;

    /**
     * @var array
     */
    public $oWert_arr = [];

    /**
     * @var array - mapping from kMerkmalWert to tmerkmalwert object
     */
    public $oWert_assoc = [];

    /**
     * @var int - how many products found that have a value of this attribute
     */
    public $nTotalResultCount = 0;

    /**
     * @var object - used by old AWA
     */
    public $oMerkmal;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Location constructor.
     * @param int  $id
     * @param bool $activeOnly
     */
    public function __construct(int $id = 0, bool $activeOnly = true)
    {
        $this->db = Shop::Container()->getDB();
        if ($id > 0) {
            $this->loadFromDB($id, $activeOnly);
        }
    }

    /**
     * @param int  $id
     * @param bool $activeOnly
     */
    private function loadFromDB(int $id, bool $activeOnly = true): void
    {
        $data = $this->db->query(
            'SELECT af.*, m.cBildpfad, COALESCE(ms.cName, m.cName) AS cName, m.cBildpfad
                FROM tauswahlassistentfrage AS af
                    JOIN tauswahlassistentgruppe as ag
                        ON ag.kAuswahlAssistentGruppe = af.kAuswahlAssistentGruppe 
                    JOIN tmerkmal AS m
                        ON m.kMerkmal = af.kMerkmal 
                    LEFT JOIN tmerkmalsprache AS ms
                        ON ms.kMerkmal = m.kMerkmal 
                            AND ms.kSprache = ag.kSprache
                WHERE af.kAuswahlAssistentFrage = ' . $id .
                    ($activeOnly ? ' AND af.nAktiv = 1' : ''),
            ReturnType::SINGLE_OBJECT
        );
        if ($data !== null && $data !== false) {
            foreach (\get_object_vars($data) as $name => $value) {
                $this->$name = $value;
            }
            $this->kAuswahlAssistentFrage  = (int)$this->kAuswahlAssistentFrage;
            $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
            $this->kMerkmal                = (int)$this->kMerkmal;
            $this->nSort                   = (int)$this->nSort;
            $this->nAktiv                  = (int)$this->nAktiv;
        }
    }

    /**
     * @param int  $groupID
     * @param bool $activeOnly
     * @return array
     */
    public function getQuestions(int $groupID, bool $activeOnly = true): array
    {
        $activeSQL = $activeOnly ? ' AND nAktiv = 1' : '';

        return $this->db->queryPrepared(
            'SELECT kAuswahlAssistentFrage AS id
                FROM tauswahlassistentfrage
                WHERE kAuswahlAssistentGruppe = :gid' . $activeSQL . '
                ORDER BY nSort',
            ['gid' => $groupID],
            ReturnType::COLLECTION
        )->map(static function ($e) use ($activeOnly) {
            return new self((int)$e->id, $activeOnly);
        })->all();
    }

    /**
     * @param bool $primary
     * @return array|bool
     */
    public function saveQuestion(bool $primary = false)
    {
        $checks = $this->checkQuestion();
        if (\count($checks) !== 0) {
            return $checks;
        }
        $ins                          = new stdClass();
        $ins->kAuswahlAssistentFrage  = $this->kAuswahlAssistentFrage;
        $ins->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
        $ins->kMerkmal                = $this->kMerkmal;
        $ins->cFrage                  = $this->cFrage;
        $ins->nSort                   = $this->nSort;
        $ins->nAktiv                  = $this->nAktiv;
        $id                           = $this->db->insert('tauswahlassistentfrage', $ins);

        if ($id > 0) {
            return $primary ? $id : true;
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function updateQuestion()
    {
        $checks = $this->checkQuestion(true);
        if (\count($checks) !== 0) {
            return $checks;
        }
        $upd                          = new stdClass();
        $upd->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
        $upd->kMerkmal                = $this->kMerkmal;
        $upd->cFrage                  = $this->cFrage;
        $upd->nSort                   = $this->nSort;
        $upd->nAktiv                  = $this->nAktiv;

        $this->db->update(
            'tauswahlassistentfrage',
            'kAuswahlAssistentFrage',
            (int)$this->kAuswahlAssistentFrage,
            $upd
        );

        return true;
    }

    /**
     * @param array $questionIDs
     * @return bool
     */
    public function deleteQuestion(array $questionIDs): bool
    {
        foreach (\array_map('\intval', $questionIDs) as $questionID) {
            $this->db->delete(
                'tauswahlassistentfrage',
                'kAuswahlAssistentFrage',
                $questionID
            );
        }

        return true;
    }

    /**
     * @param bool $update
     * @return array
     */
    public function checkQuestion(bool $update = false): array
    {
        $checks = [];
        if (\mb_strlen($this->cFrage) === 0) {
            $checks['cFrage'] = 1;
        }
        if ($this->kAuswahlAssistentGruppe === null
            || $this->kAuswahlAssistentGruppe === 0
            || $this->kAuswahlAssistentGruppe === -1
        ) {
            $checks['kAuswahlAssistentGruppe'] = 1;
        }
        if ($this->kMerkmal === null || $this->kMerkmal === 0 || $this->kMerkmal === -1) {
            $checks['kMerkmal'] = 1;
        }
        if (!$update && $this->isMerkmalTaken($this->kMerkmal, $this->kAuswahlAssistentGruppe)) {
            $checks['kMerkmal'] = 2;
        }
        if ($this->nSort <= 0) {
            $checks['nSort'] = 1;
        }
        if ($this->nAktiv !== 0 && $this->nAktiv !== 1) {
            $checks['nAktiv'] = 1;
        }

        return $checks;
    }

    /**
     * @param int $characteristicID
     * @param int $groupID
     * @return bool
     */
    private function isMerkmalTaken(int $characteristicID, int $groupID): bool
    {
        $question = $this->db->select(
            'tauswahlassistentfrage',
            'kMerkmal',
            $characteristicID,
            'kAuswahlAssistentGruppe',
            $groupID
        );

        return isset($question->kAuswahlAssistentFrage) && $question->kAuswahlAssistentFrage > 0;
    }

    /**
     * @param int  $characteristicID
     * @param bool $value
     * @return Merkmal|stdClass
     */
    public static function getMerkmal(int $characteristicID, bool $value = false)
    {
        return $characteristicID > 0
            ? new Merkmal($characteristicID, $value)
            : new stdClass();
    }
}
