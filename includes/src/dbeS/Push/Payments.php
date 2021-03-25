<?php

namespace JTL\dbeS\Push;

use JTL\DB\ReturnType;

/**
 * Class Payments
 * @package JTL\dbeS\Push
 */
final class Payments extends AbstractPush
{
    /**
     * @return array|string
     */
    public function getData()
    {
        $xml      = [];
        $payments = $this->db->query(
            "SELECT *, date_format(dZeit, '%d.%m.%Y') AS dZeit_formatted
            FROM tzahlungseingang
            WHERE cAbgeholt = 'N'
            ORDER BY kZahlungseingang",
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $count    = \count($payments);
        if ($count === 0) {
            return $xml;
        }
        foreach ($payments as $i => $payment) {
            $payments[$i . ' attr'] = $this->buildAttributes($payment);
            $payments[$i]           = $payment;
        }
        $xml['zahlungseingaenge']['tzahlungseingang'] = $payments;
        $xml['zahlungseingaenge attr']['anzahl']      = $count;

        return $xml;
    }
}
