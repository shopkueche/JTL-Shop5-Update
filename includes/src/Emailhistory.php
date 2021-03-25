<?php

namespace JTL;

use Exception;
use JTL\DB\ReturnType;
use stdClass;

/**
 * Class Emailhistory
 * @package JTL
 */
class Emailhistory
{
    /**
     * @var int
     */
    public $kEmailhistory;

    /**
     * @var int
     */
    public $kEmailvorlage;

    /**
     * @var string
     */
    public $cSubject;

    /**
     * @var string
     */
    public $cFromName;

    /**
     * @var string
     */
    public $cFromEmail;

    /**
     * @var string
     */
    public $cToName;

    /**
     * @var string
     */
    public $cToEmail;

    /**
     * @var string - date
     */
    public $dSent;

    /**
     * @param null|int    $id
     * @param null|object $data
     */
    public function __construct($id = null, $data = null)
    {
        if ((int)$id > 0) {
            $this->loadFromDB($id);
        } elseif ($data !== null && \is_object($data)) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $methodName = 'set' . \mb_substr($member, 1);
                if (\method_exists($this, $methodName)) {
                    $this->$methodName($data->$member);
                }
            }
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    protected function loadFromDB(int $id): self
    {
        $data = Shop::Container()->getDB()->select('temailhistory', 'kEmailhistory', $id);
        if (isset($data->kEmailhistory) && $data->kEmailhistory > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $this->$member = $data->$member;
            }
        }

        return $this;
    }

    /**
     * @param bool $primary
     * @return bool|int
     * @throws Exception
     */
    public function save(bool $primary = true)
    {
        $ins = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $ins->$member = $this->$member;
        }
        if (isset($ins->kEmailhistory) && (int)$ins->kEmailhistory > 0) {
            return $this->update();
        }
        unset($ins->kEmailhistory);
        $kPrim = Shop::Container()->getDB()->insert('temailhistory', $ins);
        if ($kPrim > 0) {
            return $primary ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function update(): int
    {
        $sql     = 'UPDATE temailhistory SET ';
        $set     = [];
        $members = \array_keys(\get_object_vars($this));
        if (\is_array($members) && \count($members) > 0) {
            $db = Shop::Container()->getDB();
            foreach ($members as $member) {
                $methodName = 'get' . \mb_substr($member, 1);
                if (\method_exists($this, $methodName)) {
                    $val    = $this->$methodName();
                    $mValue = $val === null
                        ? 'NULL'
                        : ("'" . $db->escape($val) . "'");
                    $set[]  = $member . ' = ' . $mValue;
                }
            }
            $sql .= \implode(', ', $set);
            $sql .= ' WHERE kEmailhistory = ' . $this->getEmailhistory();

            return $db->query($sql, ReturnType::AFFECTED_ROWS);
        }
        throw new Exception('ERROR: Object has no members!');
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('temailhistory', 'kEmailhistory', $this->getEmailhistory());
    }

    /**
     * @param string $limitSQL
     * @return array
     */
    public function getAll(string $limitSQL = ''): array
    {
        $historyData = Shop::Container()->getDB()->query(
            'SELECT * 
                FROM temailhistory 
                ORDER BY dSent DESC' . $limitSQL,
            ReturnType::ARRAY_OF_OBJECTS
        );
        $history     = [];
        foreach ($historyData as $item) {
            $item->kEmailhistory = (int)$item->kEmailhistory;
            $item->kEmailvorlage = (int)$item->kEmailvorlage;
            $history[]           = new self(null, $item);
        }

        return $history;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS nCount FROM temailhistory',
            ReturnType::SINGLE_OBJECT
        )->nCount;
    }

    /**
     * @param array $ids
     * @return bool|int
     */
    public function deletePack(array $ids)
    {
        if (\count($ids) > 0) {
            $ids = \array_map(static function ($i) {
                return (int)$i;
            }, $ids);

            return Shop::Container()->getDB()->query(
                'DELETE 
                    FROM temailhistory 
                    WHERE kEmailhistory IN (' . \implode(',', $ids) . ')',
                ReturnType::AFFECTED_ROWS
            );
        }

        return false;
    }

    /**
     * truncate the email-history-table
     * @return int
     */
    public function deleteAll(): int
    {
        Shop::Container()->getLogService()->notice('eMail-History gelöscht');

        return !Shop::Container()->getDB()->query('TRUNCATE TABLE temailhistory', ReturnType::AFFECTED_ROWS);
    }

    /**
     * @return int
     */
    public function getEmailhistory(): int
    {
        return (int)$this->kEmailhistory;
    }

    /**
     * @param int $kEmailhistory
     * @return $this
     */
    public function setEmailhistory(int $kEmailhistory): self
    {
        $this->kEmailhistory = $kEmailhistory;

        return $this;
    }

    /**
     * @return int
     */
    public function getEmailvorlage(): int
    {
        return (int)$this->kEmailvorlage;
    }

    /**
     * @param int $kEmailvorlage
     * @return $this
     */
    public function setEmailvorlage(int $kEmailvorlage): self
    {
        $this->kEmailvorlage = $kEmailvorlage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->cSubject;
    }

    /**
     * @param string $cSubject
     * @return $this
     */
    public function setSubject($cSubject): self
    {
        $this->cSubject = $cSubject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFromName(): ?string
    {
        return $this->cFromName;
    }

    /**
     * @param string $cFromName
     * @return $this
     */
    public function setFromName($cFromName): self
    {
        $this->cFromName = $cFromName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFromEmail(): ?string
    {
        return $this->cFromEmail;
    }

    /**
     * @param string $cFromEmail
     * @return $this
     */
    public function setFromEmail($cFromEmail): self
    {
        $this->cFromEmail = $cFromEmail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getToName(): ?string
    {
        return $this->cToName;
    }

    /**
     * @param string $cToName
     * @return $this
     */
    public function setToName($cToName): self
    {
        $this->cToName = $cToName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getToEmail(): ?string
    {
        return $this->cToEmail;
    }

    /**
     * @param string $cToEmail
     * @return $this
     */
    public function setToEmail($cToEmail): self
    {
        $this->cToEmail = $cToEmail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSent(): ?string
    {
        return $this->dSent;
    }

    /**
     * @param string $dSent
     * @return $this
     */
    public function setSent($dSent): self
    {
        $this->dSent = $dSent;

        return $this;
    }
}
