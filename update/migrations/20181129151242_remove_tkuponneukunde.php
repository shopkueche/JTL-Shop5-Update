<?php
/**
 * remove_tkuponneukunde
 *
 * @author mh
 * @created Thu, 29 Nov 2018 15:12:42 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181129151242
 */
class Migration_20181129151242 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove tkuponneukunde, add tkuponflag';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tkuponneukunde`');

        $this->execute('CREATE TABLE `tkuponflag` (
                          `kKuponFlag` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                          `cEmailHash` VARCHAR(255) NOT NULL,
                          `cKuponTyp`  VARCHAR(255) NOT NULL,
                          `dErstellt`  DATETIME NOT NULL,
                          PRIMARY KEY (`kKuponFlag`),
                          KEY cEmailHash_cKuponTyp (`cEmailHash`, `cKuponTyp`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

        //add flags for already used new customer coupons
        $this->execute(
            "INSERT INTO tkuponflag (cEmailHash, dErstellt, cKuponTyp)
              SELECT SHA2(LOWER(tkuponkunde.cMail), 256) AS cEmailHash,
                MAX(tkuponkunde.dErstellt) AS dErstellt,
                tkupon.cKuponTyp
              FROM tkuponkunde
                INNER JOIN tkupon
                  ON tkupon.kKupon = tkuponkunde.kKupon
              WHERE tkupon.cKuponTyp = 'neukundenkupon'
                AND tkuponkunde.cMail != ''
              GROUP BY tkuponkunde.cMail"
        );

        $this->execute('DELETE FROM `tkuponbestellung` WHERE `cKuponTyp` IS NULL');
        $this->execute('ALTER TABLE `tkuponbestellung` CHANGE COLUMN `cKuponTyp` `cKuponTyp` VARCHAR(255) NOT NULL');

        //fix nVerwendungen -> remove kKunde, allow only unique entries for each cMail + kKupon
        $this->execute('CREATE TABLE tkuponkunde_backup LIKE tkuponkunde');
        $this->execute('INSERT INTO tkuponkunde_backup SELECT * FROM tkuponkunde');
        $this->execute('TRUNCATE TABLE tkuponkunde');

        $this->execute('ALTER TABLE `tkuponkunde`
                          DROP KEY `kKupon`,
                          DROP KEY `kKunde`,
                          DROP COLUMN `kKunde`,
                          ADD UNIQUE KEY `kKupon_cMail` (`kKupon`, `cMail`)');

        $check = $this->execute(
            "INSERT INTO tkuponkunde (kKupon, cMail, nVerwendungen, dErstellt)
                SELECT tkuponkunde_backup.kKupon,
                       SHA2(LOWER(tkuponkunde_backup.cMail), 256) AS cMail,
                       COUNT(tkuponkunde_backup.cMail) AS nVerwendungen,
                       MAX(tkuponkunde_backup.dErstellt) AS dErstellt
                    FROM tkuponkunde_backup
                    INNER JOIN tkupon
                        ON tkupon.kKupon = tkuponkunde_backup.kKupon
                    WHERE tkuponkunde_backup.cMail != ''
                    GROUP BY tkuponkunde_backup.cMail, tkuponkunde_backup.kKupon"
        );
        if ($check !== 0) {
            $this->execute('DROP TABLE IF EXISTS `tkuponkunde_backup`');
        }

        $this->setLocalization('ger', 'global', 'couponErr6', 'Fehler: Maximale Verwendungen für den Kupon erreicht.');
        $this->setLocalization('eng', 'global', 'couponErr6', 'Error: Maximum usage reached for this coupon.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tkuponflag`');

        $this->execute("CREATE TABLE `tkuponneukunde` (
                          `kKuponNeukunde` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                          `kKupon`         INT(10) UNSIGNED NOT NULL,
                          `cEmail`         VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
                          `cDatenHash`     VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
                          `dErstellt`      DATETIME NOT NULL,
                          `cVerwendet`     VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
                          PRIMARY KEY (`kKuponNeukunde`),
                          KEY `cEmail` (`cEmail`,`cDatenHash`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

        $this->execute("ALTER TABLE `tkuponbestellung`
                          CHANGE COLUMN `cKuponTyp` `cKuponTyp` ENUM('prozent', 'festpreis', 'versand', 'neukunden') COLLATE utf8_unicode_ci DEFAULT NULL");

        $this->execute('ALTER TABLE `tkuponkunde`
                          DROP KEY `kKupon_cMail`,
                          ADD COLUMN `kKunde` INT(10) UNSIGNED AFTER `kKupon`,
                          ADD KEY `kKupon` (`kKupon`),
                          ADD KEY `kKunde` (`kKunde`)');

        $this->setLocalization('ger', 'global', 'couponErr6', 'Fehler: Maximale Verwendungen erreicht.');
        $this->setLocalization('eng', 'global', 'couponErr6', 'Error: Maximum usage reached');
    }
}
