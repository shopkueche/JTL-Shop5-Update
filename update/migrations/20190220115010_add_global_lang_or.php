<?php
/**
 * add_global_lang_or
 *
 * @author mh
 * @created Wed, 20 Feb 2019 11:50:10 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190220115010
 */
class Migration_20190220115010 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'add global lang var or';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'or', 'oder');
        $this->setLocalization('eng', 'global', 'or', 'or');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('or');
    }
}
