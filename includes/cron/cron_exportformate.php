<?php

use JTL\Shop;

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

/**
 * @param object $oJobQueue
 * @return bool
 * @deprecated since 5.0.0
 * @todo used by jtl google shopping
 */
function updateExportformatQueueBearbeitet($oJobQueue)
{
    if ($oJobQueue->kJobQueue > 0) {
        Shop::Container()->getDB()->delete('texportformatqueuebearbeitet', 'kJobQueue', (int)$oJobQueue->kJobQueue);

        $oExportformatQueueBearbeitet                   = new stdClass();
        $oExportformatQueueBearbeitet->kJobQueue        = $oJobQueue->kJobQueue;
        $oExportformatQueueBearbeitet->kExportformat    = $oJobQueue->kKey;
        $oExportformatQueueBearbeitet->nLimitN          = $oJobQueue->nLimitN;
        $oExportformatQueueBearbeitet->nLimitM          = $oJobQueue->nLimitM;
        $oExportformatQueueBearbeitet->nInArbeit        = $oJobQueue->nInArbeit;
        $oExportformatQueueBearbeitet->dStartZeit       = $oJobQueue->dStartZeit;
        $oExportformatQueueBearbeitet->dZuletztGelaufen = $oJobQueue->dZuletztGelaufen;

        Shop::Container()->getDB()->insert('texportformatqueuebearbeitet', $oExportformatQueueBearbeitet);

        return true;
    }

    return false;
}
