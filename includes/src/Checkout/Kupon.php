<?php

namespace JTL\Checkout;

use JTL\Alert\Alert;
use JTL\Cart\CartHelper;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Product;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Kupon
 * @package JTL\Checkout
 */
class Kupon
{
    /**
     * @var int
     */
    public $kKupon;

    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var int
     */
    public $kSteuerklasse;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var float
     */
    public $fWert;

    /**
     * @var string
     */
    public $cWertTyp;

    /**
     * @var string
     */
    public $dGueltigAb;

    /**
     * @var string
     */
    public $dGueltigBis;

    /**
     * @var float
     */
    public $fMindestbestellwert;

    /**
     * @var string
     */
    public $cCode;

    /**
     * @var int
     */
    public $nVerwendungen;

    /**
     * @var int
     */
    public $nVerwendungenBisher;

    /**
     * @var int
     */
    public $nVerwendungenProKunde;

    /**
     * @var string
     */
    public $cArtikel;

    /**
     * @var string
     */
    public $cHersteller;

    /**
     * @var string
     */
    public $cKategorien;

    /**
     * @var string
     */
    public $cKunden;

    /**
     * @var string
     */
    public $cKuponTyp;

    /**
     * @var string
     */
    public $cLieferlaender;

    /**
     * @var string
     */
    public $cZusatzgebuehren;

    /**
     * @var string
     */
    public $cAktiv;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var int
     */
    public $nGanzenWKRabattieren;

    /**
     * @var array
     */
    public $translationList;

    public const TYPE_STANDARD    = 'standard';
    public const TYPE_SHIPPING    = 'versandkupon';
    public const TYPE_NEWCUSTOMER = 'neukundenkupon';

    /**
     * Kupon constructor.
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
     * @return bool|Kupon
     */
    private function loadFromDB(int $id = 0)
    {
        $couponResult = Shop::Container()->getDB()->select('tkupon', 'kKupon', $id);

        if ($couponResult !== null && $couponResult->kKupon > 0) {
            $couponResult->translationList = $this->getTranslation((int)$couponResult->kKupon);
            foreach (\array_keys(\get_object_vars($couponResult)) as $member) {
                $this->$member = $couponResult->$member;
            }
            $this->kKupon                = (int)$this->kKupon;
            $this->kKundengruppe         = (int)$this->kKundengruppe;
            $this->kSteuerklasse         = (int)$this->kSteuerklasse;
            $this->nVerwendungen         = (int)$this->nVerwendungen;
            $this->nVerwendungenBisher   = (int)$this->nVerwendungenBisher;
            $this->nVerwendungenProKunde = (int)$this->nVerwendungenProKunde;
            $this->nGanzenWKRabattieren  = (int)$this->nGanzenWKRabattieren;

            return $this;
        }

        return false;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $cMember) {
            $ins->$cMember = $this->$cMember;
        }

        unset($ins->kKupon, $ins->translationList);
        if (empty($ins->dGueltigBis)) {
            $ins->dGueltigBis = '_DBNULL_';
        }

        $kPrim = Shop::Container()->getDB()->insert('tkupon', $ins);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                        = new stdClass();
        $upd->kKundengruppe         = $this->kKundengruppe;
        $upd->kSteuerklasse         = $this->kSteuerklasse;
        $upd->cName                 = $this->cName;
        $upd->fWert                 = $this->fWert;
        $upd->cWertTyp              = $this->cWertTyp;
        $upd->dGueltigAb            = $this->dGueltigAb;
        $upd->dGueltigBis           = empty($this->dGueltigBis) ? '_DBNULL_' : $this->dGueltigBis;
        $upd->fMindestbestellwert   = $this->fMindestbestellwert;
        $upd->cCode                 = $this->cCode;
        $upd->nVerwendungen         = $this->nVerwendungen;
        $upd->nVerwendungenBisher   = $this->nVerwendungenBisher;
        $upd->nVerwendungenProKunde = $this->nVerwendungenProKunde;
        $upd->cArtikel              = $this->cArtikel;
        $upd->cHersteller           = $this->cHersteller;
        $upd->cKategorien           = $this->cKategorien;
        $upd->cKunden               = $this->cKunden;
        $upd->cKuponTyp             = $this->cKuponTyp;
        $upd->cLieferlaender        = $this->cLieferlaender;
        $upd->cZusatzgebuehren      = $this->cZusatzgebuehren;
        $upd->cAktiv                = $this->cAktiv;
        $upd->dErstellt             = $this->dErstellt;
        $upd->nGanzenWKRabattieren  = $this->nGanzenWKRabattieren;

        return Shop::Container()->getDB()->update('tkupon', 'kKupon', (int)$this->kKupon, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tkupon', 'kKupon', (int)$this->kKupon);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setKupon(int $id): self
    {
        $this->kKupon = $id;

        return $this;
    }

    /**
     * @param int $customerGroupID
     * @return $this
     */
    public function setKundengruppe(int $customerGroupID): self
    {
        $this->kKundengruppe = $customerGroupID;

        return $this;
    }

    /**
     * @param int $kSteuerklasse
     * @return $this
     */
    public function setSteuerklasse(int $kSteuerklasse): self
    {
        $this->kSteuerklasse = $kSteuerklasse;

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
     * @param float $fWert
     * @return $this
     */
    public function setWert($fWert): self
    {
        $this->fWert = (float)$fWert;

        return $this;
    }

    /**
     * @param string $cWertTyp
     * @return $this
     */
    public function setWertTyp($cWertTyp): self
    {
        $this->cWertTyp = $cWertTyp;

        return $this;
    }

    /**
     * @param string $dGueltigAb
     * @return $this
     */
    public function setGueltigAb($dGueltigAb): self
    {
        $this->dGueltigAb = $dGueltigAb;

        return $this;
    }

    /**
     * @param string $dGueltigBis
     * @return $this
     */
    public function setGueltigBis($dGueltigBis): self
    {
        $this->dGueltigBis = $dGueltigBis;

        return $this;
    }

    /**
     * @param float $fMindestbestellwert
     * @return $this
     */
    public function setMindestbestellwert($fMindestbestellwert): self
    {
        $this->fMindestbestellwert = (float)$fMindestbestellwert;

        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code): self
    {
        $this->cCode = $code;

        return $this;
    }

    /**
     * @param int $nVerwendungen
     * @return $this
     */
    public function setVerwendungen(int $nVerwendungen): self
    {
        $this->nVerwendungen = $nVerwendungen;

        return $this;
    }

    /**
     * @param int $nVerwendungenBisher
     * @return $this
     */
    public function setVerwendungenBisher(int $nVerwendungenBisher): self
    {
        $this->nVerwendungenBisher = $nVerwendungenBisher;

        return $this;
    }

    /**
     * @param int $nVerwendungenProKunde
     * @return $this
     */
    public function setVerwendungenProKunde(int $nVerwendungenProKunde): self
    {
        $this->nVerwendungenProKunde = $nVerwendungenProKunde;

        return $this;
    }

    /**
     * @param string $cArtikel
     * @return $this
     */
    public function setArtikel($cArtikel): self
    {
        $this->cArtikel = $cArtikel;

        return $this;
    }

    /**
     * @param string $cHersteller
     * @return $this
     */
    public function setHersteller($cHersteller): self
    {
        $this->cHersteller = $cHersteller;

        return $this;
    }

    /**
     * @param string $cKategorien
     * @return $this
     */
    public function setKategorien($cKategorien): self
    {
        $this->cKategorien = $cKategorien;

        return $this;
    }

    /**
     * @param string $cKunden
     * @return $this
     */
    public function setKunden($cKunden): self
    {
        $this->cKunden = $cKunden;

        return $this;
    }

    /**
     * @param string $cKuponTyp
     * @return $this
     */
    public function setKuponTyp($cKuponTyp): self
    {
        $this->cKuponTyp = $cKuponTyp;

        return $this;
    }

    /**
     * @param string $cLieferlaender
     * @return $this
     */
    public function setLieferlaender($cLieferlaender): self
    {
        $this->cLieferlaender = $cLieferlaender;

        return $this;
    }

    /**
     * @param string $cZusatzgebuehren
     * @return $this
     */
    public function setZusatzgebuehren($cZusatzgebuehren): self
    {
        $this->cZusatzgebuehren = $cZusatzgebuehren;

        return $this;
    }

    /**
     * @param string $cAktiv
     * @return $this
     */
    public function setAktiv($cAktiv): self
    {
        $this->cAktiv = $cAktiv;

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
     * @param int $nGanzenWKRabattieren
     * @return $this
     */
    public function setGanzenWKRabattieren(int $nGanzenWKRabattieren): self
    {
        $this->nGanzenWKRabattieren = $nGanzenWKRabattieren;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getKupon(): ?int
    {
        return $this->kKupon;
    }

    /**
     * @return int|null
     */
    public function getKundengruppe(): ?int
    {
        return $this->kKundengruppe;
    }

    /**
     * @return int|null
     */
    public function getSteuerklasse(): ?int
    {
        return $this->kSteuerklasse;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @return string|float|null
     */
    public function getWert()
    {
        return $this->fWert;
    }

    /**
     * @return string|null
     */
    public function getWertTyp(): ?string
    {
        return $this->cWertTyp;
    }

    /**
     * @return string|null
     */
    public function getGueltigAb(): ?string
    {
        return $this->dGueltigAb;
    }

    /**
     * @return string|null
     */
    public function getGueltigBis(): ?string
    {
        return $this->dGueltigBis;
    }

    /**
     * @return string|float|null
     */
    public function getMindestbestellwert()
    {
        return $this->fMindestbestellwert;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->cCode;
    }

    /**
     * @return int
     */
    public function getVerwendungen(): int
    {
        return (int)($this->nVerwendungen ?? 0);
    }

    /**
     * @return int
     */
    public function getVerwendungenBisher(): int
    {
        return (int)($this->nVerwendungenBisher ?? 0);
    }

    /**
     * @return int
     */
    public function getVerwendungenProKunde(): int
    {
        return (int)($this->nVerwendungenProKunde ?? 0);
    }

    /**
     * @return string|null
     */
    public function getArtikel(): ?string
    {
        return $this->cArtikel;
    }

    /**
     * @return string|null
     */
    public function getHersteller(): ?string
    {
        return $this->cHersteller;
    }

    /**
     * @return string|null
     */
    public function getKategorien(): ?string
    {
        return $this->cKategorien;
    }

    /**
     * @return string|null
     */
    public function getKunden(): ?string
    {
        return $this->cKunden;
    }

    /**
     * @return string|null
     */
    public function getKuponTyp(): ?string
    {
        return $this->cKuponTyp;
    }

    /**
     * @return string|null
     */
    public function getLieferlaender(): ?string
    {
        return $this->cLieferlaender;
    }

    /**
     * @return string|null
     */
    public function getZusatzgebuehren(): ?string
    {
        return $this->cZusatzgebuehren;
    }

    /**
     * @return string|null
     */
    public function getAktiv(): ?string
    {
        return $this->cAktiv;
    }

    /**
     * @return string|null
     */
    public function getErstellt(): ?string
    {
        return $this->dErstellt;
    }

    /**
     * @return int
     */
    public function getGanzenWKRabattieren(): int
    {
        return (int)($this->nGanzenWKRabattieren ?? 0);
    }

    /**
     * @param string $code
     * @return bool|Kupon
     */
    public function getByCode($code = '')
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT kKupon AS id 
                FROM tkupon
                WHERE cCode = :code
                LIMIT 1',
            ['code' => $code],
            ReturnType::COLLECTION
        )->pluck('id')->transform('\intval')->mapInto(self::class)->first() ?? false;
    }

    /**
     * @param int $id
     * @return array $translationList
     */
    public function getTranslation(int $id = 0): array
    {
        $translationList = [];
        $db              = Shop::Container()->getDB();
        foreach ($_SESSION['Sprachen'] ?? [] as $language) {
            $localized                        = $db->select(
                'tkuponsprache',
                'kKupon',
                $id,
                'cISOSprache',
                $language->cISO,
                null,
                null,
                false,
                'cName'
            );
            $translationList[$language->cISO] = $localized->cName ?? '';
        }

        return $translationList;
    }

    /**
     * @return array|bool
     */
    public function getNewCustomerCoupon()
    {
        $coupons            = [];
        $newCustomerCoupons = Shop::Container()->getDB()->selectAll(
            'tkupon',
            ['cKuponTyp', 'cAktiv'],
            [self::TYPE_NEWCUSTOMER, 'Y'],
            '*',
            'fWert DESC'
        );

        foreach ($newCustomerCoupons as $newCustomerCoupon) {
            if (isset($newCustomerCoupon->kKupon) && $newCustomerCoupon->kKupon > 0) {
                $newCustomerCoupon->translationList = $this->getTranslation($newCustomerCoupon->kKupon);

                $coupons[] = $newCustomerCoupon;
            }
        }

        return $coupons;
    }

    /**
     * @param int    $len
     * @param bool   $lower
     * @param bool   $upper
     * @param bool   $numbers
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public function generateCode(
        int $len = 7,
        bool $lower = true,
        bool $upper = true,
        bool $numbers = true,
        $prefix = '',
        $suffix = ''
    ): string {
        $lowerString   = $lower ? 'abcdefghijklmnopqrstuvwxyz' : null;
        $upperString   = $upper ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : null;
        $numbersString = $numbers ? '0123456789' : null;
        $code          = '';
        $db            = Shop::Container()->getDB();
        $count         = (int)$db->query(
            'SELECT COUNT(*) AS cnt 
                FROM tkupon',
            ReturnType::SINGLE_OBJECT
        )->cnt;
        while (empty($code) || ($count === 0
                ? empty($code)
                : $db->select('tkupon', 'cCode', $code))) {
            $code = $prefix . \mb_substr(\str_shuffle(\str_repeat(
                $lowerString . $upperString . $numbersString,
                $len
            )), 0, $len) . $suffix;
        }

        return $code;
    }

    /**
     * @former altenKuponNeuBerechnen()
     * @since  5.0.0
     */
    public static function reCheck(): void
    {
        // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb, dann verwerfen und neu anlegen
        if (isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cWertTyp === 'prozent') {
            $oKupon = $_SESSION['Kupon'];
            unset($_SESSION['Kupon']);
            Frontend::getCart()->setzePositionsPreise();
            require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
            self::acceptCoupon($oKupon);
        }
    }

    /**
     * @return int
     * @former kuponMoeglich()
     * @since  5.0.0
     */
    public static function couponsAvailable(): int
    {
        $cart        = Frontend::getCart();
        $productQry  = '';
        $manufQry    = '';
        $categories  = [];
        $catQry      = '';
        $customerQry = '';
        if (isset($_SESSION['NeukundenKuponAngenommen']) && $_SESSION['NeukundenKuponAngenommen']) {
            return 0;
        }
        $db = Shop::Container()->getDB();
        foreach ($cart->PositionenArr as $item) {
            if (isset($item->Artikel->cArtNr) && \mb_strlen($item->Artikel->cArtNr) > 0) {
                $productQry .= " OR FIND_IN_SET('" .
                    \str_replace('%', '\%', $db->escape($item->Artikel->cArtNr))
                    . "', REPLACE(cArtikel, ';', ',')) > 0";
            }
            if (isset($item->Artikel->cHersteller) && \mb_strlen($item->Artikel->cHersteller) > 0) {
                $manufQry .= " OR FIND_IN_SET('" .
                    \str_replace('%', '\%', (int)$item->Artikel->kHersteller)
                    . "', REPLACE(cHersteller, ';', ',')) > 0";
            }
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && isset($item->Artikel->kArtikel)
                && $item->Artikel->kArtikel > 0
            ) {
                $productID = (int)$item->Artikel->kArtikel;
                // Kind?
                if (Product::isVariChild($productID)) {
                    $productID = Product::getParent($productID);
                }
                $categoryIDs = $db->selectAll(
                    'tkategorieartikel',
                    'kArtikel',
                    $productID,
                    'kKategorie'
                );
                foreach ($categoryIDs as $categoryID) {
                    $categoryID->kKategorie = (int)$categoryID->kKategorie;
                    if (!\in_array($categoryID->kKategorie, $categories, true)) {
                        $categories[] = $categoryID->kKategorie;
                    }
                }
            }
        }
        foreach ($categories as $category) {
            $catQry .= " OR FIND_IN_SET('{$category}', REPLACE(cKategorien, ';', ',')) > 0";
        }

        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            $customerQry = " OR FIND_IN_SET('" . $_SESSION['Kunde']->kKunde . "', REPLACE(cKunden, ';', ',')) > 0";
        }
        $ok = $db->query(
            "SELECT * FROM tkupon
                WHERE cAktiv = 'Y'
                    AND dGueltigAb <= NOW()
                    AND (dGueltigBis > NOW()
                        OR dGueltigBis IS NULL)
                    AND fMindestbestellwert <= " . $cart->gibGesamtsummeWaren(true, false) . "
                    AND (cKuponTyp = '" . self::TYPE_SHIPPING . "'
                        OR cKuponTyp = '" . self::TYPE_STANDARD . "')
                    AND (kKundengruppe = -1
                        OR kKundengruppe = 0
                        OR kKundengruppe = " . Frontend::getCustomerGroup()->getID() . ")
                    AND (nVerwendungen = 0
                        OR nVerwendungen > nVerwendungenBisher)
                    AND (cArtikel = '' " . $productQry . ")
                    AND (cHersteller IS NULL OR cHersteller = '' OR cHersteller = '-1' " . $manufQry . ")
                    AND (cKategorien = ''
                        OR cKategorien = '-1' " . $catQry . ")
                    AND (cKunden = ''
                        OR cKunden = '-1' " . $customerQry . ')',
            ReturnType::SINGLE_OBJECT
        );

        return empty($ok) ? 0 : 1;
    }

    /**
     * @param object|Kupon $coupon
     * @return array
     * @former checkeKupon()
     * @since  5.0.0
     */
    public static function checkCoupon($coupon): array
    {
        $ret = [];
        if ($coupon->cAktiv !== 'Y') {
            //not active
            $ret['ungueltig'] = 1;
        } elseif (!empty($coupon->dGueltigBis) && \date_create($coupon->dGueltigBis) < \date_create()) {
            //expired
            $ret['ungueltig'] = 2;
        } elseif (\date_create($coupon->dGueltigAb) > \date_create()) {
            //invalid at the moment
            $ret['ungueltig'] = 3;
        } elseif ($coupon->fMindestbestellwert > Frontend::getCart()->gibGesamtsummeWarenExt(
            [\C_WARENKORBPOS_TYP_ARTIKEL],
            true
        )
            || ($coupon->cWertTyp === 'festpreis'
                && $coupon->nGanzenWKRabattieren === '0'
                && $coupon->fMindestbestellwert > \gibGesamtsummeKuponartikelImWarenkorb(
                    $coupon,
                    Frontend::getCart()->PositionenArr
                )
            )
        ) {
            //minimum order value not reached for whole cart or the products which are valid for this coupon
            $ret['ungueltig'] = 4;
        } elseif ($coupon->kKundengruppe > 0
            && (int)$coupon->kKundengruppe !== Frontend::getCustomerGroup()->getID()
        ) {
            //invalid customer group
            $ret['ungueltig'] = 5;
        } elseif ($coupon->nVerwendungen > 0 && $coupon->nVerwendungen <= $coupon->nVerwendungenBisher) {
            //maximum usage reached
            $ret['ungueltig'] = 6;
        } elseif (!\warenkorbKuponFaehigArtikel($coupon, Frontend::getCart()->PositionenArr)) {
            //cart needs at least one product for which this coupon is valid
            $ret['ungueltig'] = 7;
        } elseif (!\warenkorbKuponFaehigKategorien($coupon, Frontend::getCart()->PositionenArr)) {
            //cart needs at least one category for which this coupon is valid
            $ret['ungueltig'] = 8;
        } elseif ($coupon->cKuponTyp !== self::TYPE_NEWCUSTOMER
            && (int)$coupon->cKunden !== -1
            && (!empty($_SESSION['Kunde']->kKunde
                    && \mb_strpos($coupon->cKunden, $_SESSION['Kunde']->kKunde . ';') === false)
                || !isset($_SESSION['Kunde']->kKunde)
            )
        ) {
            //invalid for account
            $ret['ungueltig'] = 9;
        } elseif ($coupon->cKuponTyp === self::TYPE_SHIPPING
            && isset($_SESSION['Lieferadresse'])
            && \mb_strpos($coupon->cLieferlaender, $_SESSION['Lieferadresse']->cLand) === false
        ) {
            //invalid for shipping country
            $ret['ungueltig'] = 10;
        } elseif (!\warenkorbKuponFaehigHersteller($coupon, Frontend::getCart()->PositionenArr)) {
            //invalid for manufacturer
            $ret['ungueltig'] = 12;
        } elseif (!empty($_SESSION['Kunde']->cMail)) {
            if ($coupon->cKuponTyp === self::TYPE_NEWCUSTOMER
                && self::newCustomerCouponUsed($_SESSION['Kunde']->cMail)
            ) {
                //email already used for a new-customer coupon
                $ret['ungueltig'] = 11;
            } elseif (!empty($coupon->nVerwendungenProKunde) && $coupon->nVerwendungenProKunde > 0) {
                //check if max usage of coupon is reached for cutomer
                $countCouponUsed = Shop::Container()->getDB()->queryPrepared(
                    'SELECT nVerwendungen
                      FROM tkuponkunde
                      WHERE kKupon = :coupon
                        AND cMail = :email',
                    [
                        'coupon' => (int)$coupon->kKupon,
                        'email'  => self::hash($_SESSION['Kunde']->cMail)
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($countCouponUsed->nVerwendungen)
                    && $countCouponUsed->nVerwendungen >= $coupon->nVerwendungenProKunde
                ) {
                    $ret['ungueltig'] = 6;
                }
            }
        }

        return $ret;
    }

    /**
     * check if a new customer coupon was already used for an email
     *
     * @param string $email
     * @return bool
     */
    public static function newCustomerCouponUsed(string $email): bool
    {
        $newCustomerCouponUsed = Shop::Container()->getDB()->queryPrepared(
            'SELECT kKuponFlag
                FROM tkuponflag
                WHERE cEmailHash = :email
                  AND cKuponTyp = :newCustomer',
            [
                'email'       => self::hash($email),
                'newCustomer' => self::TYPE_NEWCUSTOMER
            ],
            ReturnType::SINGLE_OBJECT
        );

        return !empty($newCustomerCouponUsed);
    }

    /**
     * @param Kupon|object $coupon
     * @former kuponAnnehmen()
     * @since  5.0.0
     */
    public static function acceptCoupon($coupon): void
    {
        $cart                         = Frontend::getCart();
        $logger                       = Shop::Container()->getLogService();
        $coupon->nGanzenWKRabattieren = (int)$coupon->nGanzenWKRabattieren;
        if ((!empty($_SESSION['oVersandfreiKupon']) || !empty($_SESSION['VersandKupon']) || !empty($_SESSION['Kupon']))
            && isset($_POST['Kuponcode']) && $_POST['Kuponcode']
        ) {
            $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
        }
        $couponPrice = 0;
        if ($coupon->cWertTyp === 'festpreis') {
            $couponPrice = $coupon->fWert;
            if ($coupon->fWert > $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)) {
                $couponPrice = $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true);
            }
            if ($coupon->nGanzenWKRabattieren === 0 && $coupon->fWert > \gibGesamtsummeKuponartikelImWarenkorb(
                $coupon,
                $cart->PositionenArr
            )) {
                $couponPrice = \gibGesamtsummeKuponartikelImWarenkorb($coupon, $cart->PositionenArr);
            }
        } elseif ($coupon->cWertTyp === 'prozent') {
            // Alle Positionen prüfen ob der Kupon greift und falls ja, dann Position rabattieren
            if ($coupon->nGanzenWKRabattieren === 0) {
                $productNames = [];
                if (GeneralObject::hasCount('PositionenArr', $cart)) {
                    $productPrice = 0;
                    foreach ($cart->PositionenArr as $item) {
                        $productPrice += CartHelper::checkSetPercentCouponWKPos($item, $coupon)->fPreis;
                        if (!empty(CartHelper::checkSetPercentCouponWKPos($item, $coupon)->cName)) {
                            $productNames[] = CartHelper::checkSetPercentCouponWKPos(
                                $item,
                                $coupon
                            )->cName;
                        }
                    }
                    $couponPrice = ($productPrice / 100) * (float)$coupon->fWert;
                }
            } else { //Rabatt ermitteln für den ganzen WK
                $couponPrice = ($cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true) / 100.0)
                    * $coupon->fWert;
            }
        }

        $special        = new stdClass();
        $special->cName = $coupon->translationList;
        foreach ($_SESSION['Sprachen'] as $language) {
            if ($coupon->cWertTyp === 'prozent'
                && $coupon->nGanzenWKRabattieren === 0
                && $coupon->cKuponTyp !== self::TYPE_NEWCUSTOMER
            ) {
                $special->cName[$language->cISO] .= ' ' . $coupon->fWert . '% ';
                $discount                         = Shop::Container()->getDB()->select(
                    'tsprachwerte',
                    'cName',
                    'discountForArticle',
                    'kSprachISO',
                    $language->kSprache,
                    null,
                    null,
                    false,
                    'cWert'
                );

                $special->discountForArticle[$language->cISO] = $discount->cWert;
            } elseif ($coupon->cWertTyp === 'prozent') {
                $special->cName[$language->cISO] .= ' ' . $coupon->fWert . '%';
            }
        }
        if (isset($productNames)) {
            $special->cArticleNameAffix = $productNames;
        }

        $type = \C_WARENKORBPOS_TYP_KUPON;
        if ($coupon->cKuponTyp === self::TYPE_STANDARD) {
            $_SESSION['Kupon'] = $coupon;
            if ($logger->isHandling(\JTLLOG_LEVEL_NOTICE)) {
                $logger->notice('Der Standardkupon' . \print_r($coupon, true) . ' wurde genutzt.');
            }
        } elseif ($coupon->cKuponTyp === self::TYPE_NEWCUSTOMER) {
            $type = \C_WARENKORBPOS_TYP_NEUKUNDENKUPON;
            $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_NEUKUNDENKUPON);
            $_SESSION['NeukundenKupon']           = $coupon;
            $_SESSION['NeukundenKuponAngenommen'] = true;
            //@todo: erst loggen wenn wirklich bestellt wird. hier kann noch abgebrochen werden
            if ($logger->isHandling(\JTLLOG_LEVEL_NOTICE)) {
                $logger->notice('Der Neukundenkupon' . \print_r($coupon, true) . ' wurde genutzt.');
            }
        } elseif ($coupon->cKuponTyp === self::TYPE_SHIPPING) {
            // Darf nicht gelöscht werden sondern den Preis nur auf 0 setzen!
            //$cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDPOS);
            $cart->setzeVersandfreiKupon();
            $_SESSION['VersandKupon'] = $coupon;
            $couponPrice              = 0;
            $special->cName           = $coupon->translationList;
            unset($_POST['Kuponcode']);
            $cart->erstelleSpezialPos(
                $special->cName,
                1,
                $couponPrice * -1,
                $coupon->kSteuerklasse,
                $type
            );
            if ($logger->isHandling(\JTLLOG_LEVEL_NOTICE)) {
                $logger->notice('Der Versandkupon ' . \print_r($coupon, true) . ' wurde genutzt.');
            }
        }
        if ($coupon->cWertTyp === 'prozent' || $coupon->cWertTyp === 'festpreis') {
            unset($_POST['Kuponcode']);
            $cart->erstelleSpezialPos($special->cName, 1, $couponPrice * -1, $coupon->kSteuerklasse, $type);
        }
    }

    /**
     * @former resetNeuKundenKupon()
     * @since  5.0.0
     * @param bool $priceRecalculation
     */
    public static function resetNewCustomerCoupon(bool $priceRecalculation = true): void
    {
        unset($_SESSION['NeukundenKupon'], $_SESSION['NeukundenKuponAngenommen']);
        $cart = Frontend::getCart();
        $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_NEUKUNDENKUPON);
        if ($priceRecalculation) {
            $cart->setzePositionsPreise();
        }
    }

    /**
     * @param string $strToHash
     * @param bool   $strtolower
     * @return string
     */
    public static function hash(string $strToHash, bool $strtolower = true): string
    {
        return $strToHash === ''
            ? ''
            : \hash(
                'sha256',
                $strtolower ? \mb_convert_case($strToHash, \MB_CASE_LOWER) : $strToHash
            );
    }

    /**
     * @return array
     */
    public static function getCouponTypes(): array
    {
        return [
            'newCustomer' => self::TYPE_NEWCUSTOMER,
            'standard'    => self::TYPE_STANDARD,
            'shipping'    => self::TYPE_SHIPPING
        ];
    }

    /**
     * @param int  $errorCode
     * @param bool $createAlert
     * @return null|string
     */
    public static function mapCouponErrorMessage(int $errorCode, bool $createAlert = true): ?string
    {
        switch ($errorCode) {
            case 0:
                Shop::Container()->getAlertService()->addAlert(
                    Alert::TYPE_SUCCESS,
                    Shop::Lang()->get('couponSuccess', 'global'),
                    'couponSuccess'
                );
                return null;
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
            case 12:
                $errorMessage = Shop::Lang()->get('couponErr' . $errorCode);
                break;
            case 11:
                $errorMessage = Shop::Lang()->get('invalidCouponCode', 'checkout');
                break;
            default:
                $errorMessage = Shop::Lang()->get('couponErr99');
                break;
        }
        if ($createAlert) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_DANGER,
                $errorMessage,
                'couponError',
                ['saveInSession' => true]
            );
        }

        return $errorMessage;
    }
}
