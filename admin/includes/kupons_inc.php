<?php

use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Hersteller;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Kupon;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageModel;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;

/**
 * @param array $ids
 * @return bool
 */
function loescheKupons($ids)
{
    if (!is_array($ids) || count($ids) === 0) {
        return false;
    }
    $ids       = array_map('\intval', $ids);
    $affedcted = Shop::Container()->getDB()->query(
        'DELETE tkupon, tkuponsprache, tkuponkunde, tkuponbestellung
            FROM tkupon
            LEFT JOIN tkuponsprache
              ON tkuponsprache.kKupon = tkupon.kKupon
            LEFT JOIN tkuponkunde
              ON tkuponkunde.kKupon = tkupon.kKupon
            LEFT JOIN tkuponbestellung
              ON tkuponbestellung.kKupon = tkupon.kKupon
            WHERE tkupon.kKupon IN(' . implode(',', $ids) . ')',
        ReturnType::AFFECTED_ROWS
    );

    return $affedcted >= count($ids);
}

/**
 * @param int $id
 * @return array - key = lang-iso ; value = localized coupon name
 */
function getCouponNames(int $id)
{
    $names = [];
    if (!$id) {
        return $names;
    }
    foreach (Shop::Container()->getDB()->selectAll('tkuponsprache', 'kKupon', $id) as $coupon) {
        $names[$coupon->cISOSprache] = $coupon->cName;
    }

    return $names;
}

/**
 * @param string $selectedManufacturers
 * @return array
 */
function getManufacturers($selectedManufacturers = '')
{
    $selected = Text::parseSSKint($selectedManufacturers);
    $items    = Shop::Container()->getDB()->query(
        'SELECT kHersteller, cName FROM thersteller',
        ReturnType::ARRAY_OF_OBJECTS
    );

    foreach ($items as $item) {
        $item->kHersteller = (int)$item->kHersteller;
        $manufacturer      = new Hersteller($item->kHersteller);
        $item->cName       = $manufacturer->cName;
        $item->selected    = in_array($item->kHersteller, $selected, true);
        unset($manufacturer);
    }

    return $items;
}

/**
 * @param string $selectedCategories
 * @param int    $categoryID
 * @param int    $depth
 * @return array
 */
function getCategories($selectedCategories = '', int $categoryID = 0, int $depth = 0)
{
    $selected = Text::parseSSKint($selectedCategories);
    $arr      = [];
    $items    = Shop::Container()->getDB()->selectAll(
        'tkategorie',
        'kOberKategorie',
        $categoryID,
        'kKategorie, cName'
    );
    foreach ($items as $item) {
        $item->kKategorie = (int)$item->kKategorie;
        for ($i = 0; $i < $depth; $i++) {
            $item->cName = '--' . $item->cName;
        }
        $item->selected = in_array($item->kKategorie, $selected, true);
        $arr[]          = $item;
        $arr            = array_merge($arr, getCategories($selectedCategories, $item->kKategorie, $depth + 1));
    }

    return $arr;
}

/**
 * Parse Datumsstring und formatiere ihn im DB-kompatiblen Standardformat
 *
 * @param string $string
 * @return string
 */
function normalizeDate($string)
{
    if ($string === null || $string === '') {
        return null;
    }

    $date = date_create($string);

    if ($date === false) {
        return $string;
    }

    return $date->format('Y-m-d H:i') . ':00';
}

/**
 * @param string $type
 * @param string $whereSQL
 * @param string $orderSQL
 * @param string $limitSQL
 * @return array|int|object
 */
function getRawCoupons($type = Kupon::TYPE_STANDARD, $whereSQL = '', $orderSQL = '', $limitSQL = '')
{
    return Shop::Container()->getDB()->query(
        "SELECT k.*, max(kk.dErstellt) AS dLastUse
            FROM tkupon AS k
            LEFT JOIN tkuponkunde AS kk ON kk.kKupon = k.kKupon
            WHERE cKuponTyp = '" . Shop::Container()->getDB()->escape($type) . "' " .
            ($whereSQL !== '' ? ' AND ' . $whereSQL : '') .
            'GROUP BY k.kKupon' .
            ($orderSQL !== '' ? ' ORDER BY ' . $orderSQL : '') .
            ($limitSQL !== '' ? ' LIMIT ' . $limitSQL : ''),
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * Get instances of existing coupons, each with some enhanced information that can be displayed
 *
 * @param string $type
 * @param string $whereSQL - an SQL WHERE clause (col1 = val1 AND vol2 LIKE ...)
 * @param string $orderSQL - an SQL ORDER BY clause (cName DESC)
 * @param string $limitSQL - an SQL LIMIT clause  (10,20)
 * @return array
 */
function getCoupons($type = Kupon::TYPE_STANDARD, $whereSQL = '', $orderSQL = '', $limitSQL = '')
{
    $raw = getRawCoupons($type, $whereSQL, $orderSQL, $limitSQL);
    $res = [];
    foreach ($raw as $item) {
        $res[] = getCoupon((int)$item->kKupon);
    }

    return $res;
}

/**
 * @param string $type
 * @param string $whereSQL
 * @return array
 */
function getExportableCoupons($type = Kupon::TYPE_STANDARD, $whereSQL = '')
{
    $coupons = getRawCoupons($type, $whereSQL);
    foreach ($coupons as $rawCoupon) {
        foreach (getCouponNames((int)$rawCoupon->kKupon) as $iso => $name) {
            $rawCoupon->{'cName_' . $iso} = $name;
        }
    }

    return $coupons;
}

/**
 * Get an instance of an existing coupon with some enhanced information that can be displayed
 *
 * @param int $id
 * @return Kupon $oKupon
 */
function getCoupon(int $id)
{
    $coupon = new Kupon($id);
    augmentCoupon($coupon);

    return $coupon;
}

/**
 * Enhance an existing Kupon instance with some extra information that can be displayed
 *
 * @param Kupon $coupon
 */
function augmentCoupon($coupon)
{
    $coupon->cLocalizedValue = $coupon->cWertTyp === 'festpreis'
        ? Preise::getLocalizedPriceString($coupon->fWert)
        : '';
    $coupon->cLocalizedMbw   = isset($coupon->fMindestbestellwert)
        ? Preise::getLocalizedPriceString($coupon->fMindestbestellwert)
        : '';
    $coupon->bOpenEnd        = $coupon->dGueltigBis === null;

    if (date_create($coupon->dGueltigAb) === false) {
        $coupon->cGueltigAbShort = 'ungültig';
        $coupon->cGueltigAbLong  = 'ungültig';
    } else {
        $coupon->cGueltigAbShort = date_create($coupon->dGueltigAb)->format('d.m.Y');
        $coupon->cGueltigAbLong  = date_create($coupon->dGueltigAb)->format('d.m.Y H:i');
    }

    if ($coupon->bOpenEnd) {
        $coupon->cGueltigBisShort = 'open-end';
        $coupon->cGueltigBisLong  = 'open-end';
    } elseif (date_create($coupon->dGueltigBis) === false) {
        $coupon->cGueltigBisShort = 'ungültig';
        $coupon->cGueltigBisLong  = 'ungültig';
    } elseif ($coupon->dGueltigBis === '') {
        $coupon->cGueltigBisShort = '';
        $coupon->cGueltigBisLong  = '';
    } else {
        $coupon->cGueltigBisShort = date_create($coupon->dGueltigBis)->format('d.m.Y');
        $coupon->cGueltigBisLong  = date_create($coupon->dGueltigBis)->format('d.m.Y H:i');
    }

    if ((int)$coupon->kKundengruppe === -1) {
        $coupon->cKundengruppe = '';
    } else {
        $customerGroup         = Shop::Container()->getDB()->query(
            'SELECT cName 
                FROM tkundengruppe 
                WHERE kKundengruppe = ' . $coupon->kKundengruppe,
            ReturnType::SINGLE_OBJECT
        );
        $coupon->cKundengruppe = $customerGroup->cName;
    }

    $artNos       = Text::parseSSKint($coupon->cArtikel);
    $manufactuers = Text::parseSSKint($coupon->cHersteller);
    $categories   = Text::parseSSKint($coupon->cKategorien);
    $customers    = Text::parseSSKint($coupon->cKunden);

    $coupon->cArtikelInfo    = ($coupon->cArtikel === '')
        ? ''
        : (string)count($artNos);
    $coupon->cHerstellerInfo = (empty($coupon->cHersteller) || $coupon->cHersteller === '-1')
        ? ''
        : (string)count($manufactuers);
    $coupon->cKategorieInfo  = (empty($coupon->cKategorien) || $coupon->cKategorien === '-1')
        ? ''
        : (string)count($categories);
    $coupon->cKundenInfo     = (empty($coupon->cKunden) || $coupon->cKunden === '-1')
        ? ''
        : (string)count($customers);

    $maxCreated       = Shop::Container()->getDB()->query(
        'SELECT max(dErstellt) as dLastUse
            FROM tkuponkunde
            WHERE kKupon = ' . (int)$coupon->kKupon,
        ReturnType::SINGLE_OBJECT
    );
    $coupon->dLastUse = date_create(
        is_string($maxCreated->dLastUse)
        ? $maxCreated->dLastUse
        : ''
    );
}

/**
 * Create a fresh Kupon instance with default values to be edited
 *
 * @param $cKuponTyp - Kupon::TYPE_STANDRAD, Kupon::TYPE_SHIPPING, Kupon::TYPE_NEWCUSTOMER
 * @return Kupon
 */
function createNewCoupon($cKuponTyp)
{
    $coupon                        = new Kupon();
    $coupon->cKuponTyp             = $cKuponTyp;
    $coupon->cName                 = '';
    $coupon->fWert                 = 0.0;
    $coupon->cWertTyp              = 'festpreis';
    $coupon->cZusatzgebuehren      = 'N';
    $coupon->nGanzenWKRabattieren  = 1;
    $coupon->kSteuerklasse         = 1;
    $coupon->fMindestbestellwert   = 0.0;
    $coupon->cCode                 = '';
    $coupon->cLieferlaender        = '';
    $coupon->nVerwendungen         = 0;
    $coupon->nVerwendungenProKunde = 0;
    $coupon->cArtikel              = '';
    $coupon->kKundengruppe         = -1;
    $coupon->dGueltigAb            = date_create()->format('Y-m-d H:i');
    $coupon->dGueltigBis           = '';
    $coupon->cAktiv                = 'Y';
    $coupon->cHersteller           = '-1';
    $coupon->cKategorien           = '-1';
    $coupon->cKunden               = '-1';
    $coupon->kKupon                = 0;

    augmentCoupon($coupon);

    return $coupon;
}

/**
 * Read coupon settings from the edit page form and create a Kupon instance of it
 *
 * @return Kupon
 * @throws Exception
 */
function createCouponFromInput()
{
    $coupon                        = new Kupon(Request::postInt('kKuponBearbeiten'));
    $coupon->cKuponTyp             = $_POST['cKuponTyp'];
    $coupon->cName                 = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $coupon->fWert                 = !empty($_POST['fWert']) ? (float)str_replace(',', '.', $_POST['fWert']) : null;
    $coupon->cWertTyp              = !empty($_POST['cWertTyp']) ? $_POST['cWertTyp'] : null;
    $coupon->cZusatzgebuehren      = !empty($_POST['cZusatzgebuehren']) ? $_POST['cZusatzgebuehren'] : 'N';
    $coupon->nGanzenWKRabattieren  = Request::postInt('nGanzenWKRabattieren');
    $coupon->kSteuerklasse         = !empty($_POST['kSteuerklasse']) ? (int)$_POST['kSteuerklasse'] : null;
    $coupon->fMindestbestellwert   = (float)str_replace(',', '.', $_POST['fMindestbestellwert']);
    $coupon->cCode                 = !empty($_POST['cCode']) ? $_POST['cCode'] : '';
    $coupon->cLieferlaender        = !empty($_POST['cLieferlaender'])
        ? mb_convert_case($_POST['cLieferlaender'], MB_CASE_UPPER)
        : '';
    $coupon->nVerwendungen         = Request::postInt('nVerwendungen');
    $coupon->nVerwendungenProKunde = Request::postInt('nVerwendungenProKunde');
    $coupon->cArtikel              = !empty($_POST['cArtikel']) ? ';' . trim($_POST['cArtikel'], ";\t\n\r") . ';' : '';
    $coupon->cHersteller           = '-1';
    $coupon->kKundengruppe         = Request::postInt('kKundengruppe');
    $coupon->dGueltigAb            = normalizeDate(!empty($_POST['dGueltigAb'])
        ? $_POST['dGueltigAb']
        : date_create()->format('Y-m-d H:i') . ':00');
    $coupon->dGueltigBis           = normalizeDate(!empty($_POST['dGueltigBis']) ? $_POST['dGueltigBis'] : '');
    $coupon->cAktiv                = Request::postVar('cAktiv') === 'Y' ? 'Y' : 'N';
    $coupon->cKategorien           = '-1';
    if ($coupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER) {
        $coupon->cKunden = '-1';
    }
    if (Request::postVar('bOpenEnd') === 'Y') {
        $coupon->dGueltigBis = null;
    } elseif (!empty($_POST['dDauerTage'])) {
        $coupon->dGueltigBis     = '';
        $actualTimestamp         = date_create();
        $actualTimestampEndofDay = date_time_set($actualTimestamp, 23, 59, 59);
        $setDays                 = new DateInterval('P' . $_POST['dDauerTage'] . 'D');
        $coupon->dGueltigBis     = date_add($actualTimestampEndofDay, $setDays)->format('Y-m-d H:i:s');
    }
    if (GeneralObject::hasCount('kHersteller', $_POST) && !in_array('-1', $_POST['kHersteller'])) {
        $coupon->cHersteller = Text::createSSK($_POST['kHersteller']);
    }
    if (GeneralObject::hasCount('kKategorien', $_POST) && !in_array('-1', $_POST['kKategorien'])) {
        $coupon->cKategorien = Text::createSSK($_POST['kKategorien']);
    }
    if (!empty($_POST['cKunden']) && $_POST['cKunden'] != '-1') {
        $coupon->cKunden = trim($_POST['cKunden'], ";\t\n\r") . ';';
    }
    if (isset($_POST['couponCreation'])) {
        $massCreationCoupon                  = new stdClass();
        $massCreationCoupon->cActiv          = Request::postInt('couponCreation');
        $massCreationCoupon->numberOfCoupons = ($massCreationCoupon->cActiv === 1 && !empty($_POST['numberOfCoupons']))
            ? (int)$_POST['numberOfCoupons']
            : 2;
        $massCreationCoupon->lowerCase       = ($massCreationCoupon->cActiv === 1 && !empty($_POST['lowerCase']));
        $massCreationCoupon->upperCase       = ($massCreationCoupon->cActiv === 1 && !empty($_POST['upperCase']));
        $massCreationCoupon->numbersHash     = ($massCreationCoupon->cActiv === 1 && !empty($_POST['numbersHash']));
        $massCreationCoupon->hashLength      = ($massCreationCoupon->cActiv === 1 && !empty($_POST['hashLength']))
            ? $_POST['hashLength']
            : 4;
        $massCreationCoupon->prefixHash      = ($massCreationCoupon->cActiv === 1 && !empty($_POST['prefixHash']))
            ? $_POST['prefixHash']
            : '';
        $massCreationCoupon->suffixHash      = ($massCreationCoupon->cActiv === 1 && !empty($_POST['suffixHash']))
            ? $_POST['suffixHash']
            : '';
        $coupon->massCreationCoupon          = $massCreationCoupon;
    }

    return $coupon;
}

/**
 * Get the number of existing coupons of type $cKuponTyp
 *
 * @param string $type
 * @param string $whereSQL
 * @return int
 */
function getCouponCount(string $type = Kupon::TYPE_STANDARD, string $whereSQL = ''): int
{
    return (int)Shop::Container()->getDB()->query(
        "SELECT COUNT(kKupon) AS cnt
            FROM tkupon
            WHERE cKuponTyp = '" . $type . "'" .
            ($whereSQL !== '' ? ' AND ' . $whereSQL : ''),
        ReturnType::SINGLE_OBJECT
    )->cnt;
}

/**
 * Validates the fields of a given Kupon instance
 *
 * @param Kupon $coupon
 * @return array - list of error messages
 */
function validateCoupon($coupon)
{
    $errors = [];
    if ($coupon->cName === '') {
        $errors[] = __('errorCouponNameMissing');
    }
    if (($coupon->cKuponTyp === Kupon::TYPE_STANDARD || $coupon->cKuponTyp === Kupon::TYPE_NEWCUSTOMER)
        && $coupon->fWert < 0
    ) {
        $errors[] = __('errorCouponValueNegative');
    }
    if ($coupon->fMindestbestellwert < 0) {
        $errors[] = __('errorCouponMinOrderValueNegative');
    }
    if ($coupon->cKuponTyp === Kupon::TYPE_SHIPPING && $coupon->cLieferlaender === '') {
        $errors[] = __('errorCouponISOMissing');
    }
    if (isset($coupon->massCreationCoupon)) {
        $codeLen = (int)$coupon->massCreationCoupon->hashLength
            + (int)mb_strlen($coupon->massCreationCoupon->prefixHash)
            + (int)mb_strlen($coupon->massCreationCoupon->suffixHash);
        if ($codeLen > 32) {
            $errors[] = __('errorCouponCodeLong');
        }
        if ($codeLen < 2) {
            $errors[] = __('errorCouponCodeShort');
        }
        if (!$coupon->massCreationCoupon->lowerCase
            && !$coupon->massCreationCoupon->upperCase
            && !$coupon->massCreationCoupon->numbersHash
        ) {
            $errors[] = __('errorCouponCodeOptionSelect');
        }
    } elseif (mb_strlen($coupon->cCode) > 32) {
        $errors[] = __('errorCouponCodeLong');
    }
    if ($coupon->cCode !== ''
        && !isset($coupon->massCreationCoupon)
        && ($coupon->cKuponTyp === Kupon::TYPE_STANDARD || $coupon->cKuponTyp === Kupon::TYPE_SHIPPING)
    ) {
        $queryRes = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT kKupon
                FROM tkupon
                WHERE cCode = :cCode
                    AND kKupon != :kKupon',
            [ 'cCode' => $coupon->cCode, 'kKupon' => (int)$coupon->kKupon ],
            ReturnType::SINGLE_OBJECT
        );
        if (is_object($queryRes)) {
            $errors[] = __('errorCouponCodeDuplicate');
        }
    }

    $productNos = [];
    foreach (Text::parseSSK($coupon->cArtikel) as $productNo) {
        $res = Shop::Container()->getDB()->select('tartikel', 'cArtNr', $productNo);
        if ($res === null) {
            $errors[] = sprintf(__('errorProductNumberNotFound'), $productNo);
        } else {
            $productNos[] = $productNo;
        }
    }

    $coupon->cArtikel = Text::createSSK($productNos);

    if ($coupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
        $countryHelper = Shop::Container()->getCountryService();
        foreach (Text::parseSSK($coupon->cLieferlaender) as $isoCode) {
            if ($countryHelper->getCountry($isoCode) === null) {
                $errors[] = sprintf(__('errorISOInvalid'), $isoCode);
            }
        }
    }

    $validFrom  = date_create($coupon->dGueltigAb ?? '');
    $validUntil = date_create($coupon->dGueltigBis ?? '');
    if ($validFrom === false) {
        $errors[] = __('errorPeriodBeginFormat');
    }
    if ($validUntil === false) {
        $errors[] = __('errorPeriodEndFormat');
    }

    $openEnd = $coupon->dGueltigBis === null;

    if ($validFrom !== false && $validUntil !== false && $validFrom > $validUntil && $openEnd === false) {
        $errors[] = __('errorPeriodEndAfterBegin');
    }

    return $errors;
}

/**
 * Save a new or already existing coupon in the DB
 *
 * @param Kupon $coupon
 * @param LanguageModel[] $languages
 * @return int - 0 on failure ; kKupon on success
 */
function saveCoupon($coupon, $languages)
{
    if ((int)$coupon->kKupon > 0) {
        // vorhandener Kupon
        $res = $coupon->update() === -1 ? 0 : $coupon->kKupon;
    } else {
        // neuer Kupon
        $coupon->nVerwendungenBisher = 0;
        $coupon->dErstellt           = 'NOW()';
        if (isset($coupon->massCreationCoupon)) {
            $massCreationCoupon = $coupon->massCreationCoupon;
            $coupon->kKupon     = [];
            unset($coupon->massCreationCoupon, $_POST['informieren']);
            for ($i = 1; $i <= $massCreationCoupon->numberOfCoupons; $i++) {
                if ($coupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER) {
                    $coupon->cCode = $coupon->generateCode(
                        $massCreationCoupon->hashLength,
                        $massCreationCoupon->lowerCase,
                        $massCreationCoupon->upperCase,
                        $massCreationCoupon->numbersHash,
                        $massCreationCoupon->prefixHash,
                        $massCreationCoupon->suffixHash
                    );
                }
                unset($coupon->translationList);
                $coupon->kKupon[] = (int)$coupon->save();
            }
        } else {
            if ($coupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER && $coupon->cCode === '') {
                $coupon->cCode = $coupon->generateCode();
            }
            unset($coupon->translationList);
            $coupon->kKupon = (int)$coupon->save();
        }
        $res = $coupon->kKupon;
    }

    if ($res > 0) {
        $db = Shop::Container()->getDB();
        // Kupon-Sprachen aktualisieren
        if (is_array($coupon->kKupon)) {
            foreach ($coupon->kKupon as $couponID) {
                $db->delete('tkuponsprache', 'kKupon', $couponID);
                foreach ($languages as $language) {
                    $code          = $language->getIso();
                    $postVarName   = 'cName_' . $code;
                    $localizedName = Request::postVar($postVarName, '') !== ''
                        ? htmlspecialchars($_POST[$postVarName], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                        : $coupon->cName;

                    $localized              = new stdClass();
                    $localized->kKupon      = $couponID;
                    $localized->cISOSprache = $code;
                    $localized->cName       = $localizedName;
                    $db->insert('tkuponsprache', $localized);
                }
            }
        } else {
            $db->delete('tkuponsprache', 'kKupon', $coupon->kKupon);
            foreach ($languages as $language) {
                $code          = $language->getIso();
                $postVarName   = 'cName_' . $code;
                $localizedName = Request::postVar($postVarName, '') !== ''
                    ? htmlspecialchars($_POST[$postVarName], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                    : $coupon->cName;

                $localized              = new stdClass();
                $localized->kKupon      = $coupon->kKupon;
                $localized->cISOSprache = $code;
                $localized->cName       = $localizedName;
                $db->insert('tkuponsprache', $localized);
            }
        }
    }

    return $res;
}

/**
 * Send notification emails to all customers admitted to this Kupon
 *
 * @param Kupon $coupon
 */
function informCouponCustomers($coupon)
{
    augmentCoupon($coupon);
    $db              = Shop::Container()->getDB();
    $defaultLang     = $db->select('tsprache', 'cShopStandard', 'Y');
    $defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
    $defaultOptions  = Artikel::getDefaultOptions();
    // lokalisierter Kuponwert und MBW
    $coupon->cLocalizedWert = $coupon->cWertTyp === 'festpreis'
        ? Preise::getLocalizedPriceString($coupon->fWert, $defaultCurrency, false)
        : $coupon->fWert . ' %';
    $coupon->cLocalizedMBW  = Preise::getLocalizedPriceString($coupon->fMindestbestellwert, $defaultCurrency, false);
    // kKunde-Array aller auserwaehlten Kunden
    $customerIDs     = Text::parseSSKint($coupon->cKunden);
    $customerData    = $db->query(
        'SELECT kKunde
            FROM tkunde
            WHERE TRUE
                ' . ((int)$coupon->kKundengruppe === -1
            ? 'AND TRUE'
            : 'AND kKundengruppe = ' . (int)$coupon->kKundengruppe) . '
                ' . ($coupon->cKunden === '-1'
            ? 'AND TRUE'
            : 'AND kKunde IN (' . implode(',', $customerIDs) . ')'),
        ReturnType::ARRAY_OF_OBJECTS
    );
    $productIDs      = [];
    $manufacturerIDs = Text::parseSSK($coupon->cHersteller);
    $itemNumbers     = Text::parseSSK($coupon->cArtikel);
    if (count($itemNumbers) > 0) {
        $itemNumbers = array_map(static function ($e) {
            return '"' . $e . '"';
        }, $itemNumbers);
        $productData = $db->query(
            'SELECT kArtikel
                FROM tartikel
                WHERE cArtNr IN (' . implode(',', $itemNumbers) . ')',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $productIDs  = array_map(static function ($e) {
            return (int)$e->kArtikel;
        }, $productData);
    }
    foreach ($customerData as $item) {
        $customer = new Customer((int)$item->kKunde);
        $language = Shop::Lang()->getIsoFromLangID($customer->kSprache);
        if (!$language) {
            $language = $defaultLang;
        }
        $localized  = $db->select(
            'tkuponsprache',
            ['kKupon', 'cISOSprache'],
            [$coupon->kKupon, $language->cISO]
        );
        $categories = [];
        if ($coupon->cKategorien !== '-1') {
            foreach (Text::parseSSKint($coupon->cKategorien) as $categoryID) {
                if ($categoryID <= 0) {
                    continue;
                }
                $category       = new Kategorie($categoryID, $customer->kSprache, $customer->kKundengruppe);
                $category->cURL = $category->cURLFull;
                $categories[]   = $category;
            }
        }
        $products = [];
        foreach ($productIDs as $productID) {
            $product = new Artikel();
            $product->fuelleArtikel(
                $productID,
                $defaultOptions,
                $customer->kKundengruppe,
                $customer->kSprache,
                true
            );
            $products[] = $product;
        }
        $manufacturers = [];
        foreach ($manufacturerIDs as $manufacturerID) {
            $manufacturers[] = new Hersteller($manufacturerID, $customer->kSprache);
        }
        // put all together
        $coupon->Kategorien      = $categories;
        $coupon->Artikel         = $products;
        $coupon->AngezeigterName = $localized->cName;
        $coupon->Hersteller      = $manufacturers;
        $obj                     = new stdClass();
        $obj->tkupon             = $coupon;
        $obj->tkunde             = $customer;
        $mailer                  = Shop::Container()->get(Mailer::class);
        $mail                    = new Mail();
        $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_KUPON, $obj));
    }
}

/**
 * Set all Coupons with an outdated dGueltigBis to cAktiv = 'N'
 */
function deactivateOutdatedCoupons()
{
    Shop::Container()->getDB()->query(
        "UPDATE tkupon
            SET cAktiv = 'N'
            WHERE dGueltigBis > 0
            AND dGueltigBis <= NOW()",
        ReturnType::QUERYSINGLE
    );
}

/**
 * Set all Coupons that reached nVerwendungenBisher to nVerwendungen to cAktiv = 'N'
 */
function deactivateExhaustedCoupons()
{
    Shop::Container()->getDB()->query(
        "UPDATE tkupon
            SET cAktiv = 'N'
            WHERE nVerwendungen > 0
            AND nVerwendungenBisher >= nVerwendungen",
        ReturnType::QUERYSINGLE
    );
}
