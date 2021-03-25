<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200407153000
 */
class Migration_20200407153000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add license data table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `licenses` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `data` mediumtext NOT NULL,
          `returnCode` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`)
            VALUES ('LICENSE_MANAGER', 'License Manager')");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS licenses');
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht` = 'LICENSE_MANAGER'");
    }
}
