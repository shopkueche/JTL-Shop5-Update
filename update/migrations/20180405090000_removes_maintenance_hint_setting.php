<?php
/**
 * remove-shopinfo-menu-point
 *
 * @author ms
 * @created Thu, 05 Apr 2018 09:00:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180405090000
 */
class Migration_20180405090000 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'removes maintenance hint setting';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('wartungsmodus_hinweis');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'wartungsmodus_hinweis',
            'Dieser Shop befindet sich im Wartungsmodus.',
            CONF_GLOBAL,
            'Wartungsmodus Hinweis',
            'text',
            1020,
            (object)[
                'cBeschreibung' => 'Dieser Hinweis wird Besuchern angezeigt, wenn der Shop im Wartungsmodus ist. ' .
                    'Achtung: Im Evo-Template steuern Sie diesen Text über die Sprachvariable maintenanceModeActive.',
            ]
        );
    }
}
