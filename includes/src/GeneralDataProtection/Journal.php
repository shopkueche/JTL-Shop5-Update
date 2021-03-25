<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use DateTime;
use JTL\DB\ReturnType;
use JTL\Shop;
use stdClass;

/**
 * Class Journal
 * @package JTL\GeneralDataProtection
 *
 * writes a journal of customer data changes,
 * e.g. deletion of a customer
 *
 * usage:
 * (new Journal($Somethings->Time))->save(...);
 * (new Journal(new DateTime()))->save(...);
 * (new Journal())->save(...);
 * $oJournal = new Journal([\DateTime]); for($whatever) { $oJournal->save(...); }
 */
class Journal
{
    /**
     * object-wide date at the point of instanciating
     *
     * @var object DateTime
     */
    protected $now;

    public const ISSUER_TYPE_CUSTOMER = 'CUSTOMER';

    public const ISSUER_TYPE_APPLICATION = 'APPLICATION';

    public const ISSUER_TYPE_DBES = 'DBES';

    public const ISSUER_TYPE_ADMIN = 'ADMIN';

    public const ISSUER_TYPE_PLUGIN = 'PLUGIN';

    public const ACTION_CUSTOMER_DEACTIVATED = 'CUSTOMER_DEACTIVATED';

    public const ACTION_CUSTOMER_DELETED = 'CUSTOMER_DELETED';

    /**
     * @param DateTime|null $now
     */
    public function __construct(DateTime $now = null)
    {
        $this->now = $now ?? new DateTime();
    }

    /**
     * @param string        $issuerType
     * @param int           $issuerID
     * @param string        $action
     * @param string        $message
     * @param stdClass|null $detail
     */
    public function addEntry(
        string $issuerType,
        int $issuerID,
        string $action,
        string $message = '',
        stdClass $detail = null
    ): void {
        Shop::Container()->getDB()->queryPrepared(
            'INSERT INTO tanondatajournal(cIssuer, iIssuerId, cAction, cDetail, cMessage, dEventTime)
                VALUES(:cIssuer, :iIssuerId, :cAction, :cDetail, :cMessage, :dEventTime)',
            [
                'cMessage'   => $message,
                'cDetail'    => \json_encode($detail),
                'cAction'    => $action,
                'cIssuer'    => $issuerType,
                'iIssuerId'  => $issuerID,
                'dEventTime' => $this->now->format('Y-m-d H:i:s')
            ],
            ReturnType::DEFAULT
        );
    }
}
