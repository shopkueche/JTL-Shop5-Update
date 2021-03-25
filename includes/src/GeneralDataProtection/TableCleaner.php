<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * Class TableCleaner
 * @package JTL\GeneralDataProtection
 *
 * controller of "shop customer data anonymization"
 * ("GDPR" or "Global Data Protection Rules", german: "DSGVO")
 */
class TableCleaner
{
    /**
     * object wide date at the point of instanciating
     *
     * @var object DateTime
     */
    private $now;

    /**
     * @var object Monolog\Logger
     */
    private $logger;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * anonymize methods
     * (NOTE: the order of this methods is not insignificant and "can be configured")
     *
     * @var array
     */
    private $methods = [
        ['name' => 'AnonymizeIps', 'intervalDays' => 7],
        ['name' => 'AnonymizeDeletedCustomer', 'intervalDays' => 7],
        ['name' => 'CleanupOldGuestAccounts', 'intervalDays' => 365],
        ['name' => 'CleanupCustomerRelicts', 'intervalDays' => 0],
        ['name' => 'CleanupGuestAccountsWithoutOrders', 'intervalDays' => 0],
        ['name' => 'CleanupNewsletterRecipients', 'intervalDays' => 30],
        ['name' => 'CleanupLogs', 'intervalDays' => 90],
        ['name' => 'CleanupService', 'intervalDays' => 0], // multiple own intervals
        ['name' => 'CleanupForgottenOptins', 'intervalDays' => 1]  // same as 24 hours
    ];

    /**
     * TableCleaner constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        try {
            $this->logger = Shop::Container()->getLogService();
        } catch (\Exception $e) {
            $this->logger = null;
        }
        $this->db  = Shop::Container()->getDB();
        $this->now = new \DateTime();
    }

    /**
     * run all anonymize and clean up methods
     */
    public function execute(): void
    {
        $timeStart = \microtime(true);
        foreach ($this->methods as $method) {
            $methodName = __NAMESPACE__ . '\\' . $method['name'];
            /** @var MethodInterface $instance */
            $instance = new $methodName($this->now, $method['intervalDays'], $this->db);
            $instance->execute();
            ($this->logger === null) ?: $this->logger->log(
                \JTLLOG_LEVEL_NOTICE,
                'Anonymize method executed: ' . $method['name']
            );
        }
        ($this->logger === null) ?: $this->logger->log(
            \JTLLOG_LEVEL_NOTICE,
            'Anonymizing finished in: ' . \sprintf('%01.4fs', \microtime(true) - $timeStart)
        );
    }

    /**
     * tidy up the journal
     */
    public function __destruct()
    {
        // removes journal-entries at the end of next year after their creation
        $this->db->queryPrepared(
            'DELETE FROM tanondatajournal
                WHERE dEventTime <= LAST_DAY(DATE_ADD(:pNow - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(:pNow) MONTH))',
            ['pNow' => $this->now->format('Y-m-d H:i:s')],
            ReturnType::DEFAULT
        );
    }
}
