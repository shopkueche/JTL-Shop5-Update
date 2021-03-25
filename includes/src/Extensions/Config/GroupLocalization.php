<?php

namespace JTL\Extensions\Config;

use JsonSerializable;
use JTL\Helpers\Text;
use JTL\Shop;

/**
 * Class GroupLocalization
 * @package JTL\Extensions\Config
 */
class GroupLocalization implements JsonSerializable
{
    /**
     * @var int
     */
    protected $kKonfiggruppe;

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
     * GroupLocalization constructor.
     * @param int $groupID
     * @param int $languageID
     */
    public function __construct(int $groupID = 0, int $languageID = 0)
    {
        if ($groupID > 0 && $languageID > 0) {
            $this->loadFromDB($groupID, $languageID);
        }
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array|object|string
     */
    public function jsonSerialize()
    {
        return Text::utf8_convert_recursive([
            'cName'         => $this->cName,
            'cBeschreibung' => $this->cBeschreibung
        ]);
    }

    /**
     * Loads database member into class member
     *
     * @param int $groupID primarykey
     * @param int $languageID primarykey
     */
    private function loadFromDB(int $groupID = 0, int $languageID = 0): void
    {
        $item = Shop::Container()->getDB()->select(
            'tkonfiggruppesprache',
            'kKonfiggruppe',
            $groupID,
            'kSprache',
            $languageID
        );
        if (isset($item->kKonfiggruppe, $item->kSprache) && $item->kKonfiggruppe > 0 && $item->kSprache > 0) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $this->$member = $item->$member;
            }
            $this->kSprache      = (int)$this->kSprache;
            $this->kKonfiggruppe = (int)$this->kKonfiggruppe;
        }
    }

    /**
     * @param bool $primary
     * @return bool|int
     * @deprecated since 5.0.0
     */
    public function save(bool $primary = true)
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
     * @return int
     * @deprecated since 5.0.0
     */
    public function delete(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @param int $kKonfiggruppe
     * @return $this
     */
    public function setKonfiggruppe(int $kKonfiggruppe): self
    {
        $this->kKonfiggruppe = $kKonfiggruppe;

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
        $this->cName = Shop::Container()->getDB()->escape($name);

        return $this;
    }

    /**
     * @param string $cBeschreibung
     * @return $this
     */
    public function setBeschreibung($cBeschreibung): self
    {
        $this->cBeschreibung = Shop::Container()->getDB()->escape($cBeschreibung);

        return $this;
    }

    /**
     * @return int
     */
    public function getKonfiggruppe(): int
    {
        return (int)$this->kKonfiggruppe;
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

    /**
     * @return bool
     */
    public function hatBeschreibung(): bool
    {
        return \mb_strlen($this->cBeschreibung) > 0;
    }
}
