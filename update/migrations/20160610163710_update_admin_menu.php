<?php
/**
 * update admin menu
 *
 * @author aj
 * @created Fri, 10 Jun 2016 16:37:10 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160610163710
 */
class Migration_20160610163710 extends Migration implements IMigration
{
    protected $author = 'aj';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $sql = <<<SQL
DELETE FROM `tadminmenu` WHERE `kAdminmenu` IN (63, 70, 77);
INSERT INTO `tadminmenu` (`kAdminmenueGruppe`, `cModulId`, `cLinkname`, `cURL`, `cRecht`, `nSort`) VALUES 
(11, 'core_jtl', 'Status', 'status.php', 'FILECHECK_VIEW|DBCHECK_VIEW|PERMISSIONCHECK_VIEW', '20')
SQL;

        $this->execute($sql);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $sql = <<<SQL
DELETE FROM `tadminmenu` WHERE `cURL` = 'status.php';
INSERT INTO `tadminmenu` (`kAdminmenu`, `kAdminmenueGruppe`, `cModulId`, `cLinkname`, `cURL`, `cRecht`, `nSort`) VALUES
(63, 11, 'core_jtl', 'Shopdateien-Check', 'filecheck.php', 'FILECHECK_VIEW', 20),
(70, 11, 'core_jtl', 'Datenbank-Check', 'dbcheck.php', 'DBCHECK_VIEW', 10),
(77, 11, 'core_jtl', 'Verzeichnis-Check', 'permissioncheck.php', 'PERMISSIONCHECK_VIEW', 30);
SQL;

        $this->execute($sql);
    }
}
