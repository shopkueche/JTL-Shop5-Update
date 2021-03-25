<?php declare(strict_types=1);

/**
 * @author mh
 * @created Tue, 4 Jun 2020 12:10:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200604121000
 */
class Migration_20200604121000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove tag configgroup';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('configgroup_110_tag_filter');
        $this->removeConfig('configgroup_8_box_tagcloud');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {

    }
}
