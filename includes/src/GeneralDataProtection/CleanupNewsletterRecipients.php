<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\DB\ReturnType;

/**
 * Class CleanupNewsletterRecipients
 * @package JTL\GeneralDataProtection
 *
 * Delete newsletter-registrations with no opt-in within given interval
 * (interval former "interval_clear_logs" = 90 days)
 *
 * names of the tables, we manipulate:
 *
 * `tnewsletterempfaenger`
 * `tnewsletterempfaengerhistory`
 */
class CleanupNewsletterRecipients extends Method implements MethodInterface
{
    /**
     * runs all anonymize routines
     */
    public function execute(): void
    {
        $this->cleanupNewsletters();
    }

    /**
     * delete newsletter registrations with no "opt-in"
     * within the given interval
     */
    private function cleanupNewsletters(): void
    {
        $data = $this->db->queryPrepared(
            "SELECT e.cOptCode
                FROM tnewsletterempfaenger e
                    JOIN tnewsletterempfaengerhistory h
                        ON h.cOptCode = e.cOptCode
                        AND h.cEmail = e.cEmail
                WHERE e.nAktiv = 0
                    AND h.cAktion = 'Eingetragen'
                    AND (h.dOptCode = '0000-00-00 00:00:00' OR h.dOptCode IS NULL)
                    AND h.dEingetragen <= :pDateLimit
                ORDER BY h.dEingetragen ASC
                LIMIT :pLimit",
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as $res) {
            $this->db->queryPrepared(
                'DELETE e, h
                    FROM tnewsletterempfaenger e
                       INNER JOIN tnewsletterempfaengerhistory h
                           ON h.cOptCode = e.cOptCode 
                           AND h.cEmail = e.cEmail
                    WHERE e.cOptCode = :pOpCode',
                ['pOpCode' => $res->cOptCode],
                ReturnType::DEFAULT
            );
        }
    }
}
