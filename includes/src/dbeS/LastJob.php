<?php declare(strict_types=1);

namespace JTL\dbeS;

use JTL\Catalog\ReviewReminder;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\FileSystem;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;
use JTL\Sitemap\Config\DefaultConfig;
use JTL\Sitemap\Export;
use JTL\Sitemap\ItemRenderers\DefaultRenderer;
use JTL\Sitemap\SchemaRenderers\DefaultSchemaRenderer;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class LastJob
 * @package JTL\dbeS
 */
final class LastJob
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LastJob constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
        if (!\KEEP_SYNC_FILES) {
            FileSystem::delDirRecursively(\PFAD_ROOT . \PFAD_DBES_TMP);
        }
        $this->finishStdJobs();
        $GLOBALS['nIntervall'] = \defined('LASTJOBS_INTERVALL') ? \LASTJOBS_INTERVALL : 12;
        $jobs                  = $this->getRepeatedJobs($GLOBALS['nIntervall']);
        \executeHook(\HOOK_LASTJOBS_HOLEJOBS, ['jobs' => &$jobs]);
        $config = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_SITEMAP]);
        foreach ($jobs as $job) {
            switch ((int)$job->nJob) {
                case \LASTJOBS_BEWERTUNGSERINNNERUNG:
                    $recipients = (new ReviewReminder())->getRecipients();
                    $mailer     = Shop::Container()->get(Mailer::class);
                    $mail       = new Mail();
                    foreach ($recipients as $recipient) {
                        $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BEWERTUNGERINNERUNG, $recipient));
                    }
                    $this->restartJob(\LASTJOBS_BEWERTUNGSERINNNERUNG);
                    break;
                case \LASTJOBS_SITEMAP:
                    if ($config['sitemap']['sitemap_wawiabgleich'] === 'Y') {
                        $exportConfig = new DefaultConfig(
                            $this->db,
                            $config,
                            Shop::getURL() . '/',
                            Shop::getImageBaseURL()
                        );
                        $exporter     = new Export(
                            $this->db,
                            $this->logger,
                            new DefaultRenderer(),
                            new DefaultSchemaRenderer(),
                            $config
                        );
                        $exporter->generate(
                            [CustomerGroup::getDefaultGroupID()],
                            LanguageHelper::getAllLanguages(),
                            $exportConfig->getFactories()
                        );
                        $this->restartJob(\LASTJOBS_SITEMAP);
                    }
                    break;
                case \LASTJOBS_RSS:
                    if ($config['rss']['rss_wawiabgleich'] === 'Y') {
                        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'rss_inc.php';
                        \generiereRSSXML();
                        $this->restartJob(\LASTJOBS_RSS);
                    }
                    break;
                case \LASTJOBS_GARBAGECOLLECTOR:
                    if ($config['global']['garbagecollector_wawiabgleich'] === 'Y') {
                        Shop::Container()->getDBServiceGC()->run();
                        $this->restartJob(\LASTJOBS_GARBAGECOLLECTOR);
                    }
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param int $hours
     * @return stdClass[]
     */
    private function getRepeatedJobs(int $hours): array
    {
        return $this->db->queryPrepared(
            "SELECT kJob, nJob, dErstellt
                FROM tlastjob
                WHERE cType = 'RPT'
                    AND (DATE_ADD(dErstellt, INTERVAL :hrs HOUR) < NOW())",
            ['hrs' => $hours],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return stdClass[]
     */
    private function getStdJobs(): array
    {
        return $this->db->selectAll(
            'tlastjob',
            ['cType', 'nFinished'],
            ['STD', 1],
            'kJob, nJob, cJobName, dErstellt',
            'dErstellt'
        );
    }

    /**
     * @param int $jobID
     * @return null|stdClass
     */
    private function getJob(int $jobID): ?stdClass
    {
        return $this->db->select('tlastjob', 'nJob', $jobID);
    }

    /**
     * @param int         $jobID
     * @param string|null $name
     * @return stdClass
     */
    public function run(int $jobID, $name = null): stdClass
    {
        $job = $this->getJob($jobID);
        if ($job === null) {
            $job = (object)[
                'cType'     => 'STD',
                'nJob'      => $jobID,
                'cJobName'  => $name,
                'nCounter'  => 1,
                'dErstellt' => \date('Y-m-d H:i:s'),
                'nFinished' => 0,
            ];

            $job->kJob = $this->db->insert('tlastjob', $job);
        } else {
            $job->nCounter++;
            $job->dErstellt = \date('Y-m-d H:i:s');

            $this->db->update('tlastjob', 'kJob', $job->kJob, $job);
        }

        return $job;
    }

    /**
     * @param int $jobID
     * @return int
     */
    private function restartJob(int $jobID): int
    {
        return $this->db->update(
            'tlastjob',
            'nJob',
            $jobID,
            (object)[
                'nCounter'  => 0,
                'dErstellt' => \date('Y-m-d H:i:s'),
                'nFinished' => 0,
            ]
        );
    }

    /**
     * @param int|null $jobID
     * @return int
     */
    private function finishStdJobs(int $jobID = null): int
    {
        $keys    = ['cType', 'nFinished'];
        $keyVals = ['STD', 0];

        if ($jobID > 0) {
            $keys[]    = 'nJob';
            $keyVals[] = $jobID;
        }

        $this->db->update('tlastjob', $keys, $keyVals, (object)['nFinished' => 1]);

        $keyVals[1] = 1;
        $jobs       = $this->getStdJobs();
        foreach ($jobs as $job) {
            $fileName   = \PFAD_ROOT . \PFAD_DBES . $job->cJobName . '.inc.php';
            $finishProc = $job->cJobName . '_Finish';

            if (\is_file($fileName)) {
                require_once $fileName;

                if (\function_exists($finishProc)) {
                    $finishProc();
                }
            }
        }

        return $this->db->delete('tlastjob', $keys, $keyVals);
    }
}
