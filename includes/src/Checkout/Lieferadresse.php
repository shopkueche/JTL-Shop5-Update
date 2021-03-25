<?php

namespace JTL\Checkout;

use JTL\Customer\Customer;
use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * Class Lieferadresse
 * @package JTL\Checkout
 */
class Lieferadresse extends Adresse
{
    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cAnredeLocalized;

    /**
     * @var string
     */
    public $angezeigtesLand;

    /**
     * Lieferadresse constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $kLieferadresse
     * @return Lieferadresse|int
     */
    public function loadFromDB(int $kLieferadresse)
    {
        $obj = Shop::Container()->getDB()->select('tlieferadresse', 'kLieferadresse', $kLieferadresse);

        if ($obj === null || $obj->kLieferadresse < 1) {
            return 0;
        }
        $this->fromObject($obj);
        $this->cAnredeLocalized = Customer::mapSalutation($this->cAnrede, 0, $this->kKunde);
        // Workaround for WAWI-39370
        $this->cLand           = self::checkISOCountryCode($this->cLand);
        $this->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($this->cLand);
        if ($this->kLieferadresse > 0) {
            $this->decrypt();
        }

        \executeHook(\HOOK_LIEFERADRESSE_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = self::checkISOCountryCode($obj->cLand);

        unset($obj->kLieferadresse, $obj->angezeigtesLand, $obj->cAnredeLocalized);

        $this->kLieferadresse = Shop::Container()->getDB()->insert('tlieferadresse', $obj);
        $this->decrypt();
        // Anrede mappen
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $this->kLieferadresse;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = self::checkISOCountryCode($obj->cLand);
        unset($obj->angezeigtesLand, $obj->cAnredeLocalized);
        $res = Shop::Container()->getDB()->update('tlieferadresse', 'kLieferadresse', $obj->kLieferadresse, $obj);
        $this->decrypt();
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $res;
    }

    /**
     * get shipping address
     *
     * @return array
     */
    public function gibLieferadresseAssoc(): array
    {
        return $this->kLieferadresse > 0
            ? $this->toArray()
            : [];
    }
}
