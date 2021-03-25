<?php
/**
 * Create a new table to hold the emergency-codes for the 2FA.
 *
 * @author cr
 * @created Mon, 06 Mar 2017 13:08:02 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170306130802
 */
class Migration_20170306130802 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Create a new table to hold the emergency-codes for the 2FA.';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `tadmin2facodes`(`kAdminlogin` INT(11) NOT NULL DEFAULT 0, `cEmergencyCode` VARCHAR(64) NOT NULL DEFAULT '', KEY `kAdminlogin` (`kAdminlogin`), UNIQUE KEY `cEmergencyCode` (`cEmergencyCode`) )");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE `tadmin2facodes`');
    }
}
