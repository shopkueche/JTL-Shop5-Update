<?php
/**
 * remove_unused_shopversion_template_property
 *
 * @author msc
 * @created Thu, 23 Aug 2018 14:13:05 +0200
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180823141305
 */
class Migration_20180823141305 extends Migration implements IMigration
{
    protected $author      = 'msc';
    protected $description = "Remove unused template property 'shopversion'";

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->dropColumn('ttemplate', 'shopversion');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->getDB()->query(
            'ALTER TABLE `ttemplate` ADD `shopversion` int(11) DEFAULT NULL AFTER `version`',
            ReturnType::DEFAULT
        );
    }
}
