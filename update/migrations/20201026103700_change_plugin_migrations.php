<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201026103700
 */
class Migration_20201026103700 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Plugin migrations unique key';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE `tpluginmigration` 
                ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT,
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (`id`),
                ADD UNIQUE INDEX `plgn_migid` (`kMigration` ASC, `pluginID` ASC)'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE `tpluginmigration` 
                DROP COLUMN `id`,
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (`kMigration`),
                DROP INDEX `plgn_migid`'
        );
    }
}
