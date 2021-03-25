<?php

/**
 * Add admin permissions
 *
 * @author mh
 * @created Wed, 27 Nov 2019 14:24:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191127142400
 */
class Migration_20191127142400 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add admin permissions';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) VALUES ('OPC_VIEW', 'OPC', '3');");
        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) VALUES ('FILESYSTEM_VIEW', 'Filesystem', '2');");
        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) VALUES ('CRON_VIEW', 'Cron', '2');");
        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) VALUES ('DIAGNOSTIC_VIEW', 'System diagnostics', '2');");

        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='MODULE_PRODUCTTAGS_VIEW';");
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='RMA_VIEW';");
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='SETTINGS_BOXES_VIEW';");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='OPC_VIEW';");
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='FILESYSTEM_VIEW';");
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='CRON_VIEW';");
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='DIAGNOSTIC_VIEW';");

        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) VALUES ('MODULE_PRODUCTTAGS_VIEW', 'Produkttags', '6');");
        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) VALUES ('RMA_VIEW', 'Warenrücksendung', '8');");
        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) VALUES ('SETTINGS_BOXES_VIEW', 'Boxen', '2');");
    }
}
