<?php

namespace JTL\Extensions\Download;

use JTL\Nice;
use JTL\Shop;

/**
 * Class Localization
 * @package JTL\Extensions\Download
 */
class Localization
{
    /**
     * @var int
     */
    protected $kDownload;

    /**
     * @var int
     */
    protected $kSprache;

    /**
     * @var string
     */
    protected $cName;

    /**
     * @var string
     */
    protected $cBeschreibung;

    /**
     * Localization constructor.
     * @param int $downloadID
     * @param int $languageID
     */
    public function __construct(int $downloadID = 0, int $languageID = 0)
    {
        if ($downloadID > 0 && $languageID > 0) {
            $this->loadFromDB($downloadID, $languageID);
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
     * @param int $downloadID
     * @param int $languageID
     */
    private function loadFromDB(int $downloadID, int $languageID): void
    {
        $localized = Shop::Container()->getDB()->select(
            'tdownloadsprache',
            'kDownload',
            $downloadID,
            'kSprache',
            $languageID
        );
        if ($localized !== null && (int)$localized->kDownload > 0) {
            foreach (\array_keys(\get_object_vars($localized)) as $member) {
                $this->$member = $localized->$member;
            }
            $this->kSprache  = (int)$this->kSprache;
            $this->kDownload = (int)$this->kDownload;
        }
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function save(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function update(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @param int $downloadID
     * @return $this
     */
    public function setDownload(int $downloadID): self
    {
        $this->kDownload = $downloadID;

        return $this;
    }

    /**
     * @param int $languageID
     * @return $this
     */
    public function setSprache(int $languageID): self
    {
        $this->kSprache = $languageID;

        return $this;
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
     * @param string $cBeschreibung
     * @return $this
     */
    public function setBeschreibung($cBeschreibung): self
    {
        $this->cBeschreibung = $cBeschreibung;

        return $this;
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
    public function getSprache(): int
    {
        return (int)$this->kSprache;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @return string|null
     */
    public function getBeschreibung(): ?string
    {
        return $this->cBeschreibung;
    }
}
