<?php
/**
 * correct_selection_wizard_permission
 *
 * @author mh
 * @created Fri, 12 Apr 2019 12:41:20 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190417154000
 */
class Migration_20190417154000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Unify mail template tables';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE temailvorlage ADD COLUMN `kPlugin` INT(10) UNSIGNED NOT NULL DEFAULT 0');
        foreach ($this->db->selectAll('tpluginemailvorlage', [], []) as $pluginTPL) {
            $oldID = (int)$pluginTPL->kEmailvorlage;
            unset($pluginTPL->kEmailvorlage);
            $newID = $this->db->insert('temailvorlage', $pluginTPL);
            foreach ($this->db->selectAll('tpluginemailvorlagesprache', 'kEmailvorlage', $oldID) as $loc) {
                $loc->kEmailvorlage = $newID;
                $this->db->insert('temailvorlagesprache', $loc);
            }
            foreach ($this->db->selectAll('tpluginemailvorlagespracheoriginal', 'kEmailvorlage', $oldID) as $ori) {
                $ori->kEmailvorlage = $newID;
                $this->db->insert('temailvorlagespracheoriginal', $ori);
            }
        }

        $this->execute('DROP TABLE IF EXISTS temailvorlageoriginal');
        $this->execute('DROP TABLE IF EXISTS tpluginemailvorlage');
        $this->execute('DROP TABLE IF EXISTS tpluginemailvorlagesprache');
        $this->execute('DROP TABLE IF EXISTS tpluginemailvorlagespracheoriginal');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("CREATE TABLE `temailvorlageoriginal` (
              `kEmailvorlage` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `cName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `cBeschreibung` mediumtext COLLATE utf8_unicode_ci NOT NULL,
              `cMailTyp` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text/html',
              `cModulId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `cDateiname` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              `cAktiv` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
              `nAKZ` tinyint(3) unsigned NOT NULL,
              `nAGB` tinyint(3) unsigned NOT NULL,
              `nWRB` tinyint(3) unsigned NOT NULL,
              `nFehlerhaft` tinyint(4) NOT NULL DEFAULT 0,
              `nWRBForm` tinyint(3) unsigned NOT NULL DEFAULT 0,
              `nDSE` tinyint(3) unsigned NOT NULL DEFAULT 0,
              PRIMARY KEY (`kEmailvorlage`)
            ) ENGINE=InnoDB 
            DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
        );
        $this->execute("CREATE TABLE `tpluginemailvorlage` (
              `kEmailvorlage` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `kPlugin` int(10) unsigned NOT NULL DEFAULT '0',
              `cName` varchar(255) DEFAULT NULL,
              `cBeschreibung` mediumtext NOT NULL,
              `cMailTyp` varchar(255) NOT NULL DEFAULT 'text/html',
              `cModulId` varchar(255) DEFAULT NULL,
              `cDateiname` varchar(255) NOT NULL DEFAULT '',
              `cAktiv` char(1) DEFAULT NULL,
              `nAKZ` tinyint(3) unsigned NOT NULL,
              `nAGB` tinyint(3) unsigned NOT NULL,
              `nWRB` tinyint(3) unsigned NOT NULL,
              `nWRBForm` tinyint(3) unsigned NOT NULL DEFAULT '0',
              `nDSE` tinyint(3) unsigned NOT NULL DEFAULT '0',
              `nFehlerhaft` tinyint(1) DEFAULT '0',
              PRIMARY KEY (`kEmailvorlage`)
            ) ENGINE = innodb
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci"
        );
        $this->execute("CREATE TABLE `tpluginemailvorlagesprache` (
              `kEmailvorlage` int(10) unsigned NOT NULL DEFAULT '0',
              `kSprache` tinyint(3) unsigned NOT NULL DEFAULT '0',
              `cBetreff` varchar(255) DEFAULT NULL,
              `cContentHtml` mediumtext NOT NULL,
              `cContentText` mediumtext NOT NULL,
              `cPDFS` varchar(255) NOT NULL,
              `cPDFNames` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`kEmailvorlage`,`kSprache`)
            ) ENGINE = innodb
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci"
        );
        $this->execute("CREATE TABLE `tpluginemailvorlagespracheoriginal` (
              `kEmailvorlage` int(10) unsigned NOT NULL DEFAULT '0',
              `kSprache` tinyint(3) unsigned NOT NULL DEFAULT '0',
              `cBetreff` varchar(255) DEFAULT NULL,
              `cContentHtml` mediumtext NOT NULL,
              `cContentText` mediumtext NOT NULL,
              `cPDFS` varchar(255) NOT NULL,
              `cPDFNames` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`kEmailvorlage`,`kSprache`)
            ) ENGINE = innodb
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci"
        );
        foreach ($this->db->selectAll('temailvorlage', [], []) as $tpl) {
            $pluginID = (int)$tpl->kPlugin;
            if ($pluginID === 0) {
                continue;
            }
            $oldID = (int)$tpl->kEmailvorlage;
            $newID = $this->db->insert('tpluginemailvorlage', $tpl);
            foreach ($this->db->selectAll('temailvorlagesprache', 'kEmailvorlage', $oldID) as $loc) {
                $loc->kEmailvorlage = $newID;
                $this->db->insert('tpluginemailvorlagesprache', $loc);
            }
            foreach ($this->db->selectAll('temailvorlagespracheoriginal', 'kEmailvorlage', $oldID) as $ori) {
                $ori->kEmailvorlage = $newID;
                $this->db->insert('tpluginemailvorlagespracheoriginal', $ori);
            }
            $this->db->delete('temailvorlage', 'kEmailvorlage', $oldID);
            $this->db->delete('temailvorlagesprache', 'kEmailvorlage', $oldID);
            $this->db->delete('temailvorlagespracheoriginal', 'kEmailvorlage', $oldID);
        }
        $this->execute('ALTER TABLE temailvorlage DROP COLUMN `kPlugin`');
    }
}
