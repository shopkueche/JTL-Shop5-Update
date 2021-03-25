<?php
/**
 * add_lang_rating_range_error
 *
 * @author mh
 * @created Thu, 07 Feb 2019 15:15:43 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190207151543
 */
class Migration_20190207151543 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'add lang rating range error';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'errorMessages',
            'ratingRange',
            'Die Bewertung muss eine Zahl zwischen 1 und 5 sein.'
        );
        $this->setLocalization(
            'eng',
            'errorMessages',
            'ratingRange',
            'The rating needs to be a value between 1 and 5.'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('ratingRange');
    }
}
