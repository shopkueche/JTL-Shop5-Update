<?php
/**
 * Add language var "show all reviews" to reset review filter
 *
 * @author dr
 * @created Fri, 27 Jan 2017 16:59:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170127165900
 */
class Migration_20170127165900 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add language var "show all reviews" to reset review filter';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'allReviews', 'Alle Bewertungen');
        $this->setLocalization('eng', 'product rating', 'allReviews', 'All reviews');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('allReviews');
    }
}
