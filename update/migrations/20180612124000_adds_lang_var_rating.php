<?php
/**
 * adds lang var for rating
 *
 * @author ms
 * @created Tue, 12 Jun 2018 12:40:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180612124000
 */
class Migration_20180612124000 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds lang var for rating';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'reviewsInCurrLang', 'Bewertungen in der aktuellen Sprache:');
        $this->setLocalization('eng', 'product rating', 'reviewsInCurrLang', 'Reviews in current language:');

        $this->setLocalization('ger', 'product rating', 'noReviewsInCurrLang', 'In der aktuellen Sprache gibt es keine Bewertungen.');
        $this->setLocalization('eng', 'product rating', 'noReviewsInCurrLang', 'There are no reviews in the current language.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('ratingsInCurrLang');
        $this->removeLocalization('noRatingsInCurrLang');
    }
}
