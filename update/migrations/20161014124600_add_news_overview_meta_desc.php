<?php
/**
 * add language variables for news overview meta description
 *
 * @author ms
 * @created Fri, 14 Oct 2016 12:46:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161014124600
 */
class Migration_20161014124600 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'news', 'newsMetaDesc', 'Neuigkeiten und Aktuelles zu unserem Sortiment und unserem Onlineshop');
        $this->setLocalization('eng', 'news', 'newsMetaDesc', 'News and updates to our range and our online shop');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('newsMetaDesc');
    }
}
