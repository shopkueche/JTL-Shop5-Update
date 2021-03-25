<?php

use JTL\Checkout\Bestellung;
use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * @param string $limitSQL
 * @param string $query
 * @return array
 */
function gibBestellungsUebersicht(string $limitSQL, string $query): array
{
    $orders       = [];
    $searchFilter = '';
    if (mb_strlen($query)) {
        $searchFilter = " WHERE cBestellNr LIKE '%" . Shop::Container()->getDB()->escape($query) . "%'";
    }
    $items = Shop::Container()->getDB()->query(
        'SELECT kBestellung
            FROM tbestellung
            ' . $searchFilter . '
            ORDER BY dErstellt DESC' . $limitSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($items as $item) {
        if (isset($item->kBestellung) && $item->kBestellung > 0) {
            $order = new Bestellung((int)$item->kBestellung);
            $order->fuelleBestellung(true, 0, false);
            $orders[] = $order;
        }
    }

    return $orders;
}

/**
 * @param string $query
 * @return int
 */
function gibAnzahlBestellungen($query): int
{
    $filterSQL = (mb_strlen($query) > 0)
        ? " WHERE cBestellNr LIKE '%" . Shop::Container()->getDB()->escape($query) . "%'"
        : '';

    return (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS cnt
            FROM tbestellung' . $filterSQL,
        ReturnType::SINGLE_OBJECT
    )->cnt;
}

/**
 * @param array $orderIDs
 * @return int
 */
function setzeAbgeholtZurueck(array $orderIDs): int
{
    if (!is_array($orderIDs) || count($orderIDs) === 0) {
        return 1;
    }

    $orderList = implode(',', array_map('\intval', $orderIDs));
    $customers = Shop::Container()->getDB()->query(
        'SELECT kKunde
            FROM tbestellung
            WHERE kBestellung IN(' . $orderList . ")
                AND cAbgeholt = 'Y'",
        ReturnType::ARRAY_OF_OBJECTS
    );
    if (count($customers) > 0) {
        $customerIDs = [];
        foreach ($customers as $customer) {
            $customer->kKunde = (int)$customer->kKunde;
            if (!in_array($customer->kKunde, $customerIDs, true)) {
                $customerIDs[] = $customer->kKunde;
            }
        }
        Shop::Container()->getDB()->query(
            "UPDATE tkunde
                SET cAbgeholt = 'N'
                WHERE kKunde IN(" . implode(',', $customerIDs) . ')',
            ReturnType::DEFAULT
        );
    }
    Shop::Container()->getDB()->query(
        "UPDATE tbestellung
            SET cAbgeholt = 'N'
            WHERE kBestellung IN(" . $orderList . ")
                AND cAbgeholt = 'Y'",
        ReturnType::DEFAULT
    );
    Shop::Container()->getDB()->query(
        "UPDATE tzahlungsinfo
            SET cAbgeholt = 'N'
            WHERE kBestellung IN(" . $orderList . ")
                AND cAbgeholt = 'Y'",
        ReturnType::DEFAULT
    );

    return -1;
}
