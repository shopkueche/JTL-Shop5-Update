<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\Customer\Customer;
use JTL\DB\ReturnType;

/**
 * Class CleanupGuestAccountsWithoutOrders
 * @package JTL\GeneralDataProtection
 *
 * Deleted guest accounts with no open orders
 *
 * names of the tables, we manipulate:
 *
 * `tkunde`
 */
class CleanupGuestAccountsWithoutOrders extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute(): void
    {
        $this->cleanupCustomers();
    }

    /**
     * delete not registered customers (relicts)
     */
    private function cleanupCustomers(): void
    {
        $guestAccounts = $this->db->queryPrepared(
            "SELECT kKunde
                FROM tkunde
                WHERE nRegistriert = 0
                  AND cAbgeholt ='Y'
                LIMIT :pLimit",
            ['pLimit' => $this->workLimit],
            ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($guestAccounts as $guestAccount) {
            (new Customer((int)$guestAccount->kKunde))->deleteAccount(Journal::ISSUER_TYPE_APPLICATION, 0, true);
        }
    }
}
