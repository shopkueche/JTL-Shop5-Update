<?php
/**
 * Change database version to semantic versioning
 *
 * @author msc
 * @created Thu, 23 Aug 2018 15:48:32 +0200
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180823154832
 */
class Migration_20180823154832 extends Migration implements IMigration
{
    protected $author      = 'msc';
    protected $description = 'Change database version to semantic versioning';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->getDB()->query(
            'ALTER TABLE `tversion` CHANGE `nVersion` `nVersion` varchar(20) NOT NULL',
            ReturnType::DEFAULT
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->getDB()->query(
            'ALTER TABLE `tversion` CHANGE `nVersion` `nVersion` int(10) DEFAULT NULL',
            ReturnType::DEFAULT
        );
    }
}
