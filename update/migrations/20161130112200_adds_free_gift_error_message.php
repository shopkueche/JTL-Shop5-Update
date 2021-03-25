<?php
/**
 * adds free gift error message
 *
 * @author ms
 * @created Wed, 30 Nov 2016 11:22:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161130112200
 */
class Migration_20161130112200 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'errorMessages', 'freegiftsMinimum', 'Der Gratisartikel-Mindestbestellwert ist nicht erreicht.');
        $this->setLocalization('eng', 'errorMessages', 'freegiftsMinimum', 'Minimum shopping cart value not reached for this free gift.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('freegiftsMinimum');
    }
}
