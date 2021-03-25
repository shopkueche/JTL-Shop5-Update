<?php
/**
 * removed bilder_hochskalieren
 *
 * @author aj
 * @created Tue, 19 Apr 2016 11:43:35 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160419114335
 */
class Migration_20160419114335 extends Migration implements IMigration
{
    protected $author = 'aj';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('bilder_hochskalieren');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
