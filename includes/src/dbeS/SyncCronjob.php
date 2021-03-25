<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class SyncCronjob
 * @package JTL\dbeS
 */
class SyncCronjob extends NetSyncHandler
{
    /**
     * @param int $request
     */
    protected function request($request): void
    {
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'smartyinclude.php';
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'exportformat_inc.php';
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'exportformat_queue_inc.php';
        switch ($request) {
            case NetSyncRequest::CRONJOBSTATUS:
                require_once \PFAD_ROOT . \PFAD_INCLUDES . 'cron_inc.php';
                $exports = \holeExportformatCron();
                if (\is_array($exports)) {
                    foreach ($exports as &$job) {
                        $job = new CronjobStatus(
                            $job->kCron,
                            $job->cName,
                            $job->dStart_de,
                            $job->nAlleXStd,
                            (int)$job->oJobQueue->nLimitN,
                            (int)$job->nAnzahlArtikel->nAnzahl,
                            $job->dLetzterStart_de,
                            $job->dNaechsterStart_de
                        );
                    }
                    unset($job);
                }

                self::throwResponse(NetSyncResponse::OK, $exports);
                break;

            case NetSyncRequest::CRONJOBHISTORY:
                $exports = \holeExportformatQueueBearbeitet(24 * 7);
                if (\is_array($exports)) {
                    foreach ($exports as &$job) {
                        $job = new CronjobHistory(
                            $job->cName,
                            $job->cDateiname,
                            $job->nLimitN,
                            $job->dZuletztGelaufen_DE
                        );
                    }
                    unset($job);
                }

                self::throwResponse(NetSyncResponse::OK, $exports);
                break;

            case NetSyncRequest::CRONJOBTRIGGER:
                $bCronManuell = true;
                require_once \PFAD_ROOT . \PFAD_INCLUDES . 'cron_inc.php';

                self::throwResponse(NetSyncResponse::OK, true);
                break;
        }
    }
}
