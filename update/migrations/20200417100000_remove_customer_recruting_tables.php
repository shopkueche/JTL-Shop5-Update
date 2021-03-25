<?php
/**
 * Remove customer recruting tables
 *
 * @author mh
 * @created Fri, 17 Apr 2020 10:00:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200417100000
 */
class Migration_20200417100000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove customer recruting tables';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tkundenwerbenkunden`');
        $this->execute('DROP TABLE IF EXISTS `tkundenwerbenkundenbonus`');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'CREATE TABLE IF NOT EXISTS `tkundenwerbenkunden` (
              `kKundenWerbenKunden` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `kKunde` INT(10) UNSIGNED NOT NULL,
              `cVorname` VARCHAR(255) NOT NULL,
              `cNachname` VARCHAR(255) NOT NULL,
              `cEmail` VARCHAR(255) NOT NULL,
              `nRegistriert` TINYINT(3) UNSIGNED NOT NULL,
              `nGuthabenVergeben` TINYINT(3) UNSIGNED NOT NULL,
              `fGuthaben` DOUBLE NOT NULL,
              `dErstellt` DATETIME NOT NULL,
              PRIMARY KEY (`kKundenWerbenKunden`),
              KEY `cEmail` (`cEmail`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
        $this->execute(
            'CREATE TABLE IF NOT EXISTS `tkundenwerbenkundenbonus` (
              `kKundenWerbenKundenBonus` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `kKunde` INT(10) UNSIGNED NOT NULL,
              `fGuthaben` DOUBLE DEFAULT NULL,
              `nBonuspunkte` INT(10) UNSIGNED DEFAULT NULL,
              `dErhalten` DATETIME NOT NULL,
              PRIMARY KEY (`kKundenWerbenKundenBonus`),
              KEY `kKunde` (`kKunde`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }
}
