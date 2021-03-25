<?php
/**
 * removes legal hint from language variable shareYourRatingGuidelines
 *
 * @author ms
 * @created Thu, 06 Oct 2016 14:35:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161006143500
 */
class Migration_20161006143500 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'shareYourRatingGuidelines', 'Teilen Sie uns Ihre Meinung mit');
        $this->setLocalization('eng', 'product rating', 'shareYourRatingGuidelines', 'Share your experience');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'product rating', 'shareYourRatingGuidelines', 'Teilen Sie uns Ihre Meinung mit. Bitte beachten Sie dabei unsere Artikelbewertungs-Richtlinien');
        $this->setLocalization('eng', 'product rating', 'shareYourRatingGuidelines', 'Share your experience and please be aware about our post guidelines');
    }
}
