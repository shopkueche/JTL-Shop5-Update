<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use DateInterval;
use DateTime;
use JTL\Campaign;
use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Shop;
use stdClass;

/**
 * Class Newsletter
 * @package JTL\Cron\Job
 */
final class Newsletter extends Job
{
    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);
        if (\JOBQUEUE_LIMIT_M_NEWSLETTER > 0) {
            $this->setLimit((int)\JOBQUEUE_LIMIT_M_NEWSLETTER);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $configuredDelay = (Shop::getConfigValue(\CONF_NEWSLETTER, 'newsletter_send_delay') > 0) ?: 0;
        $lastSending     = $this->db->select(
            'tnewsletter',
            'kNewsletter',
            $this->getForeignKeyID(),
            null,
            null,
            null,
            null,
            false,
            'dLastSendings'
        )->dLastSendings;
        // the first call always sends mails. each following call only sends mails depending on the lastSending-time
        if (empty($lastSending)) {
            $downStep    = $configuredDelay + 1;
            $lastSending = (new DateTime())->add(DateInterval::createFromDateString('-' . $downStep . ' hour'));
        } else {
            $lastSending = DateTime::createFromFormat('Y-m-d H:i:s', $lastSending);
        }
        if ((new DateTime())->sub(new DateInterval('PT' . $configuredDelay . 'H')) < $lastSending) {
            return $this;
        }
        if (($jobData = $this->getJobData()) === null) {
            return $this;
        }
        $instance = new \JTL\Newsletter\Newsletter($this->db, Shop::getSettings([\CONF_NEWSLETTER]));
        $instance->initSmarty();
        $productIDs      = $instance->getKeys($jobData->cArtikel, true);
        $manufacturerIDs = $instance->getKeys($jobData->cHersteller);
        $categoryIDs     = $instance->getKeys($jobData->cKategorie);
        $customerGroups  = $instance->getKeys($jobData->cKundengruppe);
        $campaign        = new Campaign((int)$jobData->kKampagne);
        if (\count($customerGroups) === 0) {
            $this->setFinished(true);

            return $this;
        }
        $products   = [];
        $categories = [];
        foreach ($customerGroups as $groupID) {
            $products[$groupID]   = $instance->getProducts($productIDs, $campaign, $groupID, (int)$jobData->kSprache);
            $categories[$groupID] = $instance->getCategories($categoryIDs, $campaign);
        }
        $manufacturers = $instance->getManufacturers($manufacturerIDs, $campaign, (int)$jobData->kSprache);
        $recipients    = $this->getRecipients($jobData, $queueEntry, $customerGroups);
        if (\count($recipients) > 0) {
            $shopURL = Shop::getURL();
            foreach ($recipients as $recipient) {
                $recipient->cLoeschURL = $shopURL . '/?oc=' . $recipient->cLoeschCode;
                $cgID                  = (int)$recipient->kKundengruppe > 0 ? (int)$recipient->kKundengruppe : 0;
                $instance->send(
                    $jobData,
                    $recipient,
                    $products[$cgID],
                    $manufacturers,
                    $categories[$cgID],
                    $campaign,
                    $recipient->kKunde > 0 ? new Customer((int)$recipient->kKunde) : null
                );
                $this->db->update(
                    'tnewsletterempfaenger',
                    'kNewsletterEmpfaenger',
                    (int)$recipient->kNewsletterEmpfaenger,
                    (object)['dLetzterNewsletter' => \date('Y-m-d H:m:s')]
                );
                ++$queueEntry->tasksExecuted;
            }
            $rowUpdate                = new stdClass();
            $rowUpdate->dLastSendings = (new DateTime())->format('Y-m-d H:i:s');
            $this->db->update('tnewsletter', 'kNewsletter', $this->getForeignKeyID(), $rowUpdate);
            $this->setFinished(false);
        } else {
            $this->setFinished(true);
            $this->db->delete('tcron', 'cronID', $this->getCronID());
        }

        return $this;
    }

    /**
     * @param stdClass   $jobData
     * @param QueueEntry $queueEntry
     * @param array      $customerGroups
     * @return array
     */
    private function getRecipients($jobData, $queueEntry, array $customerGroups): array
    {
        $cgSQL = 'AND (tkunde.kKundengruppe IN (' . \implode(',', $customerGroups) . ') ';
        if (\in_array(0, $customerGroups, true)) {
            $cgSQL .= ' OR tkunde.kKundengruppe IS NULL';
        }
        $cgSQL .= ')';

        return $this->db->query(
            'SELECT tkunde.kKundengruppe, tkunde.kKunde, tsprache.cISO, tnewsletterempfaenger.kNewsletterEmpfaenger,
            tnewsletterempfaenger.cAnrede, tnewsletterempfaenger.cVorname, tnewsletterempfaenger.cNachname,
            tnewsletterempfaenger.cEmail, tnewsletterempfaenger.cLoeschCode
                FROM tnewsletterempfaenger
                LEFT JOIN tsprache
                    ON tsprache.kSprache = tnewsletterempfaenger.kSprache
                LEFT JOIN tkunde
                    ON tkunde.kKunde = tnewsletterempfaenger.kKunde
                WHERE tnewsletterempfaenger.kSprache = ' . (int)$jobData->kSprache . '
                    AND tnewsletterempfaenger.nAktiv = 1 ' . $cgSQL . '
                ORDER BY tnewsletterempfaenger.kKunde
                LIMIT ' . $queueEntry->tasksExecuted . ', ' . $queueEntry->taskLimit,
            ReturnType::ARRAY_OF_OBJECTS
        );
    }
}
