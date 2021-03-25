<?php
/**
 * change_of_the_language_variable_dimensions
 *
 * @author msc
 * @created Mon, 25 Apr 2016 09:14:20 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160425091420
 */
class Migration_20160425091420 extends Migration implements IMigration
{
    protected $author = 'msc';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'dimensions', 'Abmessungen(LxBxH)');
        $this->setLocalization('eng', 'productDetails', 'dimensions', 'Dimensions(LxWxH)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'productDetails', 'dimensions', 'Abmessungen');
        $this->setLocalization('eng', 'productDetails', 'dimensions', 'Dimensions');
    }
}
