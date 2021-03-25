<?php
/**
 * add_lang_key_dimensions_2d
 *
 * @author msc
 * @created Tue, 17 May 2016 14:04:32 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160517140432
 */
class Migration_20160517140432 extends Migration implements IMigration
{
    protected $author = 'msc';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'dimensions2d', 'Abmessungen (L&times;H)');
        $this->setLocalization('eng', 'productDetails', 'dimensions2d', 'Dimensions (L&times;H)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'dimensions2d'");
    }
}
