<?php
/**
 * Add language variables for birthday date
 *
 * @author dr
 * @created Mon, 16 Jan 2017 14:56:38 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170116145638
 */
class Migration_20170116145638 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add language variables for birthday date';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'account data', 'birthdayFormat', 'TT.MM.JJJJ');
        $this->setLocalization('eng', 'account data', 'birthdayFormat', 'DD.MM.YYYY');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('birthdayFormat');
    }
}
