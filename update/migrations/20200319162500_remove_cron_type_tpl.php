<?php
/**
 * Remove cron type tpl
 *
 * @author fm
 * @created Thu, 19 Mar 2020 16:25:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200319162500
 */
class Migration_20200319162500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove cron type tpl';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $useCron = $this->fetchOne(
            "SELECT ttemplate.name, ttemplateeinstellungen.cWert
                FROM ttemplateeinstellungen
                JOIN ttemplate USING (cTemplate)
                WHERE ttemplateeinstellungen.cName = 'use_cron';"
        );
        $this->setConfig(
            'cron_type',
            ($useCron->cWert  ?? 'N') === 'N' ? 'N' : 's2s',
            CONF_CRON,
            'Pseudo-Cron Methode',
            'selectbox',
            1,
            (object)[
                'cBeschreibung' => 'Welche Methode soll verwendet werden?',
                'inputOptions'  => [
                    'N'   => 'keine',
                    's2s' => 'Curl Server-to-Server',
                ],
            ],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'cron_type',
            'N',
            CONF_CRON,
            'Pseudo-Cron Methode',
            'selectbox',
            1,
            (object)[
                'cBeschreibung' => 'Welche Methode soll verwendet werden?',
                'inputOptions'  => [
                    'N'   => 'keine',
                    'tpl' => 'Template-gesteuert',
                    's2s' => 'Curl Server-to-Server',
                ],
            ],
            true
        );
    }
}
