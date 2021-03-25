<?php
/**
 * Remove sorting by availability
 *
 * @author mh
 * @created Thu, 16 Apr 2020 12:09:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200416120900
 */
class Migration_20200416120900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove sorting by availability';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('suche_sortierprio_lagerbestand');

        $this->removeLocalization('sortAvailability', 'global');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'suche_sortierprio_lagerbestand',
            '6',
            CONF_ARTIKELUEBERSICHT,
            'Priorität der Suchtreffersortierung: Verfügbarkeit',
            'number',
            240,
            (object)[
                'cBeschreibung' => '0 - diese Sortiermöglichkeit wird nicht angeboten. Sortierung nach Lagerbestand'
            ]
        );
        $this->setLocalization('ger', 'global', 'sortAvailability', 'Lagerbestand');
        $this->setLocalization('eng', 'global', 'sortAvailability', 'Stock level');
    }
}
