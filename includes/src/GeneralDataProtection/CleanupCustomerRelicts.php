<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\DB\ReturnType;

/**
 * Class CleanupCustomerRelicts
 * @package JTL\GeneralDataProtection
 *
 * clean up multiple tables at each run
 * (normaly one times a day)
 *
 * names of the tables, we manipulate:
 *
 * `tbesucherarchiv`
 * `tkundenattribut`
 * `tkundenkontodaten`
 * `tzahlungsinfo`
 * `tlieferadresse`
 * `trechnungsadresse`
 *
 * data will be removed here!
 */
class CleanupCustomerRelicts extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute(): void
    {
        $this->cleanupVisitorArchive();
        $this->cleanupCustomerAttributes();
        $this->cleanupPaymentInformation();
        $this->cleanupCustomerAccountData();
        $this->cleanupDeliveryAddresses();
        $this->cleanupBillingAddresses();
    }

    /**
     * delete visitors in the visitors archive immediately (at each run of the cron),
     * without a valid customer account
     */
    private function cleanupVisitorArchive(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tbesucherarchiv
            WHERE
                kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tbesucherarchiv.kKunde)
                LIMIT :pLimit',
            ['pLimit' => $this->workLimit],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete customer attributes
     * for which there are no valid customer accounts
     */
    private function cleanupCustomerAttributes(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tkundenattribut
            WHERE
                NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tkundenattribut.kKunde)
            LIMIT :pLimit',
            ['pLimit' => $this->workLimit],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete orphaned payment information about customers
     * which have no valid account
     */
    private function cleanupPaymentInformation(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tzahlungsinfo
            WHERE
                kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tzahlungsinfo.kKunde)
            LIMIT :pLimit',
            ['pLimit' => $this->workLimit],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete orphaned bank account information of customers
     * which have no valid account
     */
    private function cleanupCustomerAccountData(): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tkundenkontodaten
            WHERE
                kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tkundenkontodaten.kKunde)
            LIMIT :pLimit',
            ['pLimit' => $this->workLimit],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete delivery addresses
     * which assigned to no valid customer account
     *
     * (ATTENTION: no work limit possible here)
     */
    private function cleanupDeliveryAddresses(): void
    {
        $this->db->query(
            "DELETE k
            FROM tlieferadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cAbgeholt = 'Y'
                AND b.cStatus IN (" . \BESTELLUNG_STATUS_VERSANDT . ', ' . \BESTELLUNG_STATUS_STORNO . ')
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = k.kKunde)',
            ReturnType::DEFAULT
        );
    }

    /**
     * delete billing addresses witout valid customer accounts
     *
     * (ATTENTION: no work limit possible here)
     */
    private function cleanupBillingAddresses(): void
    {
        $this->db->query(
            "DELETE k
            FROM trechnungsadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE b.cAbgeholt = 'Y'
                AND b.cStatus IN (" . \BESTELLUNG_STATUS_VERSANDT . ', ' . \BESTELLUNG_STATUS_STORNO . ')
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = k.kKunde)',
            ReturnType::DEFAULT
        );
    }
}
