<?php
/**
 * add_delivery_status_lang
 *
 * @author mh
 * @created Fri, 03 Aug 2018 12:52:35 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180803125235
 */
class Migration_20180803125235 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'add delivery status lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'productDetails',
            'productUnsaleable',
            'Dieser Artikel ist derzeit nicht verfügbar. Ob und wann dieser Artikel wieder erhältlich ist, steht nicht fest.'
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'productUnsaleable',
            'This product is currently unavailable. It is uncertain whether or when the product will be available again.'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('productUnsaleable');
    }
}
