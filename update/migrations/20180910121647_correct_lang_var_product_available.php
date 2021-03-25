<?php
/**
 * correct_lang_var_product_available
 *
 * @author mh
 * @created Mon, 10 Sep 2018 12:16:47 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180910121647
 */
class Migration_20180910121647 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Correct lang var productAvailable';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'productAvailable', 'verfügbar');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'global', 'productAvailable', 'Artikel verfügbar ab');
    }
}
