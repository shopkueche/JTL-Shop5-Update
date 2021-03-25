<?php
/**
 * Increase versandklasse varchar size
 *
 * @author mh
 * @created Fr, 17 July 2020 12:36:00 +0100
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200717123600
 */
class Migration_20200717123600 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Increase versandklasse varchar size';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tversandart` DROP INDEX cVersandklassen');
        $this->execute('ALTER TABLE `tversandart` DROP INDEX cKundengruppen');
        $this->execute('ALTER TABLE `tversandart` MODIFY COLUMN `cVersandklassen` VARCHAR (8192)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tversandart` MODIFY COLUMN `cVersandklassen` VARCHAR (255)');
        if (!$this->getDB()->query(
            "SHOW INDEX FROM tversandart WHERE KEY_NAME = 'cVersandklassen'",
            ReturnType::SINGLE_OBJECT
        )) {
            $this->execute('ALTER TABLE `tversandart` ADD INDEX cVersandklassen (cVersandklassen)');
        }
        if (!$this->getDB()->query(
            "SHOW INDEX FROM tversandart WHERE KEY_NAME = 'cKundengruppen'",
            ReturnType::SINGLE_OBJECT
        )) {
            $this->execute('ALTER TABLE `tversandart` ADD INDEX cKundengruppen (cKundengruppen)');
        }
    }
}
