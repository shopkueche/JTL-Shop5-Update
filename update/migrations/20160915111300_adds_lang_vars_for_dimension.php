<?php
/**
 * add_language_variable_descriptionview
 *
 * @author ms
 * @created Tue, 15 Sep 2016 11:13:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160915111300
 */
class Migration_20160915111300 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'dimension_height', 'Höhe');
        $this->setLocalization('eng', 'productDetails', 'dimension_height', 'height');

        $this->setLocalization('ger', 'productDetails', 'dimension_length', 'Länge');
        $this->setLocalization('eng', 'productDetails', 'dimension_length', 'length');

        $this->setLocalization('ger', 'productDetails', 'dimension_width', 'Breite');
        $this->setLocalization('eng', 'productDetails', 'dimension_width', 'width');

        $this->removeLocalization('dimensions2d');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('dimension_height');
        $this->removeLocalization('dimension_length');
        $this->removeLocalization('dimension_width');

        $this->setLocalization('ger', 'productDetails', 'dimensions2d', 'Abmessungen (L&times;H)');
        $this->setLocalization('eng', 'productDetails', 'dimensions2d', 'Dimensions (L&times;H)');
    }
}
