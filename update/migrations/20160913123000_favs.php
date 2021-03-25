<?php
/**
 * Add language variables for the new pagination
 *
 * @author fm
 * @created Mon, 12 Sep 2016 17:30:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160913123000
 */
class Migration_20160913123000 extends Migration implements IMigration
{
    protected $author      = 'aj';
    protected $description = 'Create admin favorite table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("
            CREATE TABLE `tadminfavs` (
             `kAdminfav` int(10) unsigned NOT NULL AUTO_INCREMENT,
             `kAdminlogin` int(10) unsigned NOT NULL,
             `cTitel` varchar(255) NOT NULL,
             `cUrl` varchar(255) NOT NULL,
             `nSort` int(10) unsigned NOT NULL DEFAULT '0',
             PRIMARY KEY (`kAdminfav`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE `tadminfavs`');
    }
}
