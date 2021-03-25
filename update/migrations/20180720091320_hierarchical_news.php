<?php
/**
 * hierarchical_news
 *
 * @author mh
 * @created Fri, 20 Jul 2018 09:13:20 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180720091320
 */
class Migration_20180720091320 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Hierarchical news';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE `tnewskategorie`
                ADD COLUMN `kParent` INT(10) NOT NULL DEFAULT 0'
        );
        $this->execute('ALTER TABLE `tnewskategorie` ADD INDEX `kParent` (kParent)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tnewskategorie`DROP COLUMN `kParent`');
    }
}
