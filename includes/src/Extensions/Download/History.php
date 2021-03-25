<?php

namespace JTL\Extensions\Download;

use JTL\DB\ReturnType;
use JTL\Nice;
use JTL\Shop;
use stdClass;

/**
 * Class History
 * @package JTL\Extensions\Download
 */
class History
{
    /**
     * @var int
     */
    protected $kDownloadHistory;

    /**
     * @var int
     */
    protected $kDownload;

    /**
     * @var int
     */
    protected $kKunde;

    /**
     * @var int
     */
    protected $kBestellung;

    /**
     * @var string
     */
    protected $dErstellt;

    /**
     * History constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_DOWNLOADS);
    }

    /**
     * @param int $id
     */
    private function loadFromDB(int $id): void
    {
        $history = Shop::Container()->getDB()->select(
            'tdownloadhistory',
            'kDownloadHistory',
            $id
        );
        if ($history !== null && (int)$history->kDownloadHistory > 0) {
            $members = \array_keys(\get_object_vars($history));
            if (\is_array($members) && \count($members) > 0) {
                foreach ($members as $member) {
                    $this->$member = $history->$member;
                }
                $this->kDownload        = (int)$this->kDownload;
                $this->kDownloadHistory = (int)$this->kDownloadHistory;
                $this->kKunde           = (int)$this->kKunde;
                $this->kBestellung      = (int)$this->kBestellung;
            }
        }
    }

    /**
     * @param int $downloadID
     * @return array
     * @deprecated since 5.0.0
     */
    public static function getHistorys(int $downloadID): array
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);

        return self::getHistory($downloadID);
    }

    /**
     * @param int $downloadID
     * @return array
     */
    public static function getHistory(int $downloadID): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT kDownloadHistory AS id 
                FROM tdownloadhistory
                WHERE kDownload = :dlid
                ORDER BY dErstellt DESC',
            ['dlid' => $downloadID],
            ReturnType::COLLECTION
        )->pluck('id')->transform(static function ($e) {
            return (int)$e;
        })->mapInto(self::class)->toArray();
    }

    /**
     * @param int $customerID
     * @param int $orderID
     * @return array
     */
    public static function getOrderHistory(int $customerID, int $orderID = 0): array
    {
        $history = [];
        if ($orderID > 0 || $customerID > 0) {
            $where = 'kBestellung = ' . $orderID;
            if ($orderID > 0) {
                $where .= ' AND kKunde = ' . $customerID;
            }

            $data = Shop::Container()->getDB()->query(
                'SELECT kDownload, kDownloadHistory
                     FROM tdownloadhistory
                     WHERE ' . $where . '
                     ORDER BY dErstellt DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($data as $item) {
                if (!isset($history[$item->kDownload]) || !\is_array($history[$item->kDownload])) {
                    $history[$item->kDownload] = [];
                }
                $history[$item->kDownload][] = new self((int)$item->kDownloadHistory);
            }
        }

        return $history;
    }

    /**
     * @param bool $primary
     * @return bool|int
     */
    public function save(bool $primary = false)
    {
        $ins = $this->kopiereMembers();
        unset($ins->kDownloadHistory);

        $historyID = Shop::Container()->getDB()->insert('tdownloadhistory', $ins);
        if ($historyID > 0) {
            return $primary ? $historyID : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd              = new stdClass();
        $upd->kDownload   = $this->kDownload;
        $upd->kKunde      = $this->kKunde;
        $upd->kBestellung = $this->kBestellung;
        $upd->dErstellt   = $this->dErstellt;

        return Shop::Container()->getDB()->update(
            'tdownloadhistory',
            'kDownloadHistory',
            (int)$this->kDownloadHistory,
            $upd
        );
    }

    /**
     * @param int $kDownloadHistory
     * @return $this
     */
    public function setDownloadHistory(int $kDownloadHistory): self
    {
        $this->kDownloadHistory = $kDownloadHistory;

        return $this;
    }

    /**
     * @param int $kDownload
     * @return $this
     */
    public function setDownload(int $kDownload): self
    {
        $this->kDownload = $kDownload;

        return $this;
    }

    /**
     * @param int $customerID
     * @return $this
     */
    public function setKunde(int $customerID): self
    {
        $this->kKunde = $customerID;

        return $this;
    }

    /**
     * @param int $orderID
     * @return $this
     */
    public function setBestellung(int $orderID): self
    {
        $this->kBestellung = $orderID;

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = $dErstellt;

        return $this;
    }

    /**
     * @return int
     */
    public function getDownloadHistory(): int
    {
        return (int)$this->kDownloadHistory;
    }

    /**
     * @return int
     */
    public function getDownload(): int
    {
        return (int)$this->kDownload;
    }

    /**
     * @return int
     */
    public function getKunde(): int
    {
        return (int)$this->kKunde;
    }

    /**
     * @return int
     */
    public function getBestellung(): int
    {
        return (int)$this->kBestellung;
    }

    /**
     * @return string|null
     */
    public function getErstellt(): ?string
    {
        return $this->dErstellt;
    }

    /**
     * @return stdClass
     */
    private function kopiereMembers(): stdClass
    {
        $obj     = new stdClass();
        $members = \array_keys(\get_object_vars($this));
        if (\is_array($members) && \count($members) > 0) {
            foreach ($members as $member) {
                $obj->$member = $this->$member;
            }
        }

        return $obj;
    }
}
