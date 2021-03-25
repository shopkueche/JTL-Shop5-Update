<?php
/**
 * Cron improvements
 *
 * @author fm
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190108120800
 */
class Migration_20190108120800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Cron improvements';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE `tcron` 
            CHANGE COLUMN `kCron` `cronID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
            CHANGE COLUMN `kKey` `foreignKeyID` INT(10) UNSIGNED DEFAULT NULL,
            CHANGE COLUMN `cKey` `foreignKey` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
            CHANGE COLUMN `cTabelle` `tableName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
            CHANGE COLUMN `cName` `name` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `cJobArt` `jobType` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `nAlleXStd` `frequency` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN `dStart` `startDate` DATETIME NULL DEFAULT NULL,
            CHANGE COLUMN `dStartZeit` `startTime` TIME NULL DEFAULT NULL,
            CHANGE COLUMN `dLetzterStart` `lastStart` DATETIME NULL DEFAULT NULL,
            ADD COLUMN `lastFinish` VARCHAR(45) NULL AFTER `lastStart`;");
        $this->execute("ALTER TABLE `tjobqueue` 
            CHANGE COLUMN `kJobQueue` `jobQueueID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE COLUMN `kCron` `cronID` INT(10) UNSIGNED NOT NULL,
            CHANGE COLUMN `kKey` `foreignKeyID` INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE COLUMN `cJobArt` `jobType` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `cTabelle` `tableName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
            CHANGE COLUMN `cKey` `foreignKey` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
            CHANGE COLUMN `nLimitN` `tasksExecuted` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN `nLimitM` `taskLimit` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN `nLastArticleID` `lastProductID` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN `nInArbeit` `isRunning` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN `dStartZeit` `startTime` DATETIME NULL DEFAULT NULL,
            CHANGE COLUMN `dZuletztGelaufen` `lastStart` DATETIME NULL DEFAULT NULL,
            ADD COLUMN `lastFinish` DATETIME NULL DEFAULT NULL;");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("ALTER TABLE `tcron`
            CHANGE COLUMN `cronID` `kCron` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE COLUMN `foreignKeyID` `kKey` INT(10) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE COLUMN `foreignKey` `cKey` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `tableName` `cTabelle` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `name` `cName` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `jobType` `cJobArt` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `frequency` `nAlleXStd` INT(10) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE COLUMN `startDate` `dStart` DATETIME NULL DEFAULT NULL,
            CHANGE COLUMN `startTime` `dStartZeit` TIME NULL DEFAULT NULL,
            CHANGE COLUMN `lastStart` `dLetzterStart` DATETIME NULL DEFAULT NULL,
            DROP COLUMN `lastFinish`;");
        $this->execute("ALTER TABLE `tjobqueue` 
            CHANGE COLUMN `jobQueueID` `kJobQueue` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE COLUMN `cronID` `kCron` INT(10) UNSIGNED NOT NULL,
            CHANGE COLUMN `foreignKeyID` `kKey` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN `jobType` `cJobArt` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `tableName` `cTabelle` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `foreignKey` `cKey` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL,
            CHANGE COLUMN `taskLimit` `nLimitM` INT(10) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE COLUMN `tasksExecuted` `nLimitN` INT(10) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE COLUMN `lastProductID` `nLastArticleID` INT(10) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE COLUMN `isRunning` `nInArbeit` INT(10) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE COLUMN `startTime` `dStartZeit` DATETIME NULL DEFAULT NULL,
            CHANGE COLUMN `lastStart` `dZuletztGelaufen` DATETIME NULL DEFAULT NULL,
            DROP COLUMN `lastFinish`;");
    }
}
