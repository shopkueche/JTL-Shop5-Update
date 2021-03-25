<?php

namespace JTL\Checkout;

use JTL\Customer\Customer;
use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * Class Rechnungsadresse
 * @package JTL\Checkout
 */
class Rechnungsadresse extends Adresse
{
    /**
     * @var int
     */
    public $kRechnungsadresse;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cUSTID;

    /**
     * @var string
     */
    public $cWWW;

    /**
     * @var string
     */
    public $cAnredeLocalized;

    /**
     * @var string
     */
    public $angezeigtesLand;

    /**
     * Rechnungsadresse constructor.
     *
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return int|Rechnungsadresse
     */
    public function loadFromDB(int $id)
    {
        $obj = Shop::Container()->getDB()->select('trechnungsadresse', 'kRechnungsadresse', $id);

        if ($obj === null || $obj->kRechnungsadresse < 1) {
            return 0;
        }
        $this->fromObject($obj);
        $this->kKunde            = (int)$this->kKunde;
        $this->kRechnungsadresse = (int)$this->kRechnungsadresse;
        $this->cAnredeLocalized  = Customer::mapSalutation($this->cAnrede, 0, $this->kKunde);
        // Workaround for WAWI-39370
        $this->cLand           = self::checkISOCountryCode($this->cLand);
        $this->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($this->cLand);
        if ($this->kRechnungsadresse > 0) {
            $this->decrypt();
        }

        \executeHook(\HOOK_RECHNUNGSADRESSE_CLASS_LOADFROMDB);

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

        unset($obj->kRechnungsadresse, $obj->angezeigtesLand, $obj->cAnredeLocalized);

        $this->kRechnungsadresse = Shop::Container()->getDB()->insert('trechnungsadresse', $obj);
        $this->decrypt();
        // Anrede mappen
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $this->kRechnungsadresse;
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

        $res = Shop::Container()->getDB()->update(
            'trechnungsadresse',
            'kRechnungsadresse',
            $obj->kRechnungsadresse,
            $obj
        );
        $this->decrypt();
        // Anrede mappen
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $res;
    }

    /**
     * @return array
     */
    public function gibRechnungsadresseAssoc(): array
    {
        if ($this->kRechnungsadresse > 0) {
            // wawi needs these attributes in exactly this order
            return [
                'cAnrede'           => $this->cAnrede,
                'cTitel'            => $this->cTitel,
                'cVorname'          => $this->cVorname,
                'cNachname'         => $this->cNachname,
                'cFirma'            => $this->cFirma,
                'cStrasse'          => $this->cStrasse,
                'cAdressZusatz'     => $this->cAdressZusatz,
                'cPLZ'              => $this->cPLZ,
                'cOrt'              => $this->cOrt,
                'cBundesland'       => $this->cBundesland,
                'cLand'             => $this->cLand,
                'cTel'              => $this->cTel,
                'cMobil'            => $this->cMobil,
                'cFax'              => $this->cFax,
                'cUSTID'            => $this->cUSTID,
                'cWWW'              => $this->cWWW,
                'cMail'             => $this->cMail,
                'cZusatz'           => $this->cZusatz,
                'cAnredeLocalized'  => $this->cAnredeLocalized,
                'cHausnummer'       => $this->cHausnummer,
                // kXXX variables will be set as attribute nodes by syncinclude.php::buildAttributes
                'kRechnungsadresse' => $this->kRechnungsadresse,
                'kKunde'            => $this->kKunde,
            ];
        }

        return [];
    }
}
