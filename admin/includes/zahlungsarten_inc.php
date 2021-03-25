<?php

use JTL\Checkout\Zahlungsart;
use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * @param int $paymentMethodID
 * @return array
 */
function getNames(int $paymentMethodID)
{
    $res = [];
    if (!$paymentMethodID) {
        return $res;
    }
    $items = Shop::Container()->getDB()->selectAll('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID);
    foreach ($items as $item) {
        $res[$item->cISOSprache] = $item->cName;
    }

    return $res;
}

/**
 * @param int $paymentMethodID
 * @return array
 */
function getshippingTimeNames(int $paymentMethodID)
{
    $res = [];
    if (!$paymentMethodID) {
        return $res;
    }
    $items = Shop::Container()->getDB()->selectAll('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID);
    foreach ($items as $item) {
        $res[$item->cISOSprache] = $item->cGebuehrname;
    }

    return $res;
}

/**
 * @param int $paymentMethodID
 * @return array
 */
function getHinweisTexte(int $paymentMethodID)
{
    $messages = [];
    if (!$paymentMethodID) {
        return $messages;
    }
    $localizations = Shop::Container()->getDB()->selectAll(
        'tzahlungsartsprache',
        'kZahlungsart',
        $paymentMethodID
    );
    foreach ($localizations as $localization) {
        $messages[$localization->cISOSprache] = $localization->cHinweisText;
    }

    return $messages;
}

/**
 * @param int $paymentMethodID
 * @return array
 */
function getHinweisTexteShop(int $paymentMethodID)
{
    $messages = [];
    if (!$paymentMethodID) {
        return $messages;
    }
    $localizations = Shop::Container()->getDB()->selectAll(
        'tzahlungsartsprache',
        'kZahlungsart',
        $paymentMethodID
    );
    foreach ($localizations as $localization) {
        $messages[$localization->cISOSprache] = $localization->cHinweisTextShop;
    }

    return $messages;
}

/**
 * @param Zahlungsart $paymentMethod
 * @return array
 */
function getGesetzteKundengruppen($paymentMethod)
{
    $ret = [];
    if (!isset($paymentMethod->cKundengruppen) || !$paymentMethod->cKundengruppen) {
        $ret[0] = true;

        return $ret;
    }
    foreach (explode(';', $paymentMethod->cKundengruppen) as $customerGroupID) {
        $ret[$customerGroupID] = true;
    }

    return $ret;
}

/**
 * @param string $query
 * @return array $allShippingsByName
 */
function getPaymentMethodsByName($query)
{
    $paymentMethodsByName = [];
    foreach (explode(',', $query) as $string) {
        // Leerzeichen löschen
        trim($string);
        // Nur Eingaben mit mehr als 2 Zeichen
        if (mb_strlen($string) > 2) {
            $data = Shop::Container()->getDB()->queryPrepared(
                'SELECT za.kZahlungsart, za.cName
                    FROM tzahlungsart AS za
                    LEFT JOIN tzahlungsartsprache AS zs 
                        ON zs.kZahlungsart = za.kZahlungsart
                        AND zs.cName LIKE :search
                    WHERE za.cName LIKE :search 
                    OR zs.cName LIKE :search',
                ['search' => '%' . $string . '%'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            // Berücksichtige keine fehlerhaften Eingaben
            if (!empty($data)) {
                if (count($data) > 1) {
                    foreach ($data as $paymentMethodByName) {
                        $paymentMethodsByName[$paymentMethodByName->kZahlungsart] = $paymentMethodByName;
                    }
                } else {
                    $paymentMethodsByName[$data[0]->kZahlungsart] = $data[0];
                }
            }
        }
    }

    return $paymentMethodsByName;
}
