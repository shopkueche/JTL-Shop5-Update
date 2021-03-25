<?php

namespace JTL\dbeS\Push;

use JTL\DB\ReturnType;

/**
 * Class Data
 * @package JTL\dbeS\Push
 */
final class Data extends AbstractPush
{
    private const LIMIT_UPLOADQUEUE = 100;

    private const LIMIT_AVAILABILITY_MSGS = 100;

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $xml     = [];
        $current = $this->db->query(
            "SELECT *
            FROM tverfuegbarkeitsbenachrichtigung
            WHERE cAbgeholt = 'N'
            LIMIT " . self::LIMIT_AVAILABILITY_MSGS,
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $count   = \count($current);
        if ($count > 0) {
            $xml['tverfuegbarkeitsbenachrichtigung attr']['anzahl'] = $count;
            for ($i = 0; $i < $xml['tverfuegbarkeitsbenachrichtigung attr']['anzahl']; $i++) {
                $current[$i . ' attr'] = $this->buildAttributes($current[$i]);
                $this->db->query(
                    "UPDATE tverfuegbarkeitsbenachrichtigung
                    SET cAbgeholt = 'Y'
                    WHERE kVerfuegbarkeitsbenachrichtigung = " .
                    (int)$current[$i . ' attr']['kVerfuegbarkeitsbenachrichtigung'],
                    ReturnType::DEFAULT
                );
            }
            $xml['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'] = $current;
        }
        $queueData = $this->db->query(
            'SELECT *
            FROM tuploadqueue
            LIMIT ' . self::LIMIT_UPLOADQUEUE,
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $count     = \count($queueData);
        if ($count > 0) {
            $xml['queueddata']['uploadqueue']['tuploadqueue'] = $queueData;
            $xml['tuploadqueue attr']['anzahl']               = $count;
            foreach ($queueData as $i => $item) {
                $xml['queueddata']['uploadqueue']['tuploadqueue'][$i . ' attr'] = $this->buildAttributes($item);
            }
        }

        return $xml;
    }
}
