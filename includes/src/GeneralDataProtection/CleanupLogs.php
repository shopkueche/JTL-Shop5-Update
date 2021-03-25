<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\DB\ReturnType;

/**
 * Class CleanupLogs
 * @package JTL\GeneralDataProtection
 *
 * Delete old logs containing personal data.
 * (interval former "interval_clear_logs" = 90 days)
 *
 * names of the tables, we manipulate:
 *
 * `temailhistory`
 * `tkontakthistory`
 * `tzahlungslog`
 * `tproduktanfragehistory`
 * `tverfuegbarkeitsbenachrichtigung`
 * `tjtllog`
 * `tzahlungseingang`
 * `tkundendatenhistory`
 * `tfloodprotect`
 */
class CleanupLogs extends Method implements MethodInterface
{
    /**
     * runs all anonymize routines
     */
    public function execute(): void
    {
        $this->cleanupEmailHistory();
        $this->cleanupContactHistory();
        $this->cleanupFloodProtect();
        $this->cleanupPaymentLogEntries();
        $this->cleanupProductInquiries();
        $this->cleanupAvailabilityInquiries();
        $this->cleanupLogs();
        $this->cleanupPaymentConfirmations();
        $this->cleanupCustomerDataHistory();
    }

    /**
     * delete email history
     * older than given interval
     */
    private function cleanupEmailHistory(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM temailhistory
                WHERE dSent <= :pDateLimit
                ORDER BY dSent ASC
                LIMIT :pLimit',
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete customer history
     * older than given interval
     */
    private function cleanupContactHistory(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tkontakthistory
                WHERE dErstellt <= :pDateLimit
                ORDER BY dErstellt ASC
                LIMIT :pLimit',
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete upload request history
     * older than given interval
     */
    private function cleanupFloodProtect(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tfloodprotect
                WHERE dErstellt <= :pDateLimit
                ORDER BY dErstellt ASC
                LIMIT :pLimit',
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete log entries of payments
     * older than the given interval
     */
    private function cleanupPaymentLogEntries(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tzahlungslog
            WHERE dDatum <= :pDateLimit
            ORDER BY dDatum ASC
            LIMIT :pLimit',
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete product inquiries of customers
     * older than the given interval
     */
    private function cleanupProductInquiries(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tproduktanfragehistory
            WHERE dErstellt <= :pDateLimit
            ORDER BY dErstellt ASC
            LIMIT :pLimit',
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete availability demands of customers
     * older than the given interval
     */
    private function cleanupAvailabilityInquiries(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tverfuegbarkeitsbenachrichtigung
            WHERE dErstellt <= :pDateLimit
            ORDER BY dErstellt ASC
            LIMIT :pLimit',
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete jtl log entries
     * older than the given interval
     */
    private function cleanupLogs(): void
    {
        $this->db->queryPrepared(
            "DELETE FROM tjtllog
                WHERE (cLog LIKE '%@%' OR cLog LIKE '%kKunde%')
                    AND dErstellt <= :pDateLimit
                ORDER BY dErstellt ASC
                LIMIT :pLimit",
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete payment confirmations of customers
     * not collected by 'wawi' and older than the given interval
     */
    private function cleanupPaymentConfirmations(): void
    {
        $this->db->queryPrepared(
            "DELETE FROM tzahlungseingang
                WHERE cAbgeholt != 'Y'
                    AND dZeit <= :pDateLimit
                ORDER BY dZeit ASC
                LIMIT :pLimit",
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete customer data history
     * CONSIDER: using no time base or limit here!
     *
     * (§76 BDSG Abs(4) : "Die Protokolldaten sind am Ende des auf deren Generierung folgenden Jahres zu löschen.")
     */
    private function cleanupCustomerDataHistory(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tkundendatenhistory
                WHERE dErstellt < MAKEDATE(YEAR(:pNow) - 1, 1)
                ORDER BY dErstellt ASC
                LIMIT :pLimit',
            [
                'pNow'   => $this->now->format('Y-m-d H:i:s'),
                'pLimit' => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }
}
