<?php
/**
 * Changes salutions
 *
 * @author ms
 * @created Wed, 15 Feb 2017 16:18:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170215161800
 */
class Migration_20170215161800 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'changes female salutation to ms and adds general salutation';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('eng', 'global', 'salutationW', 'Ms');

        $this->setLocalization('ger', 'global', 'salutationGeneral', 'Frau/Herr');
        $this->setLocalization('eng', 'global', 'salutationGeneral', 'Ms/Mr');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('eng', 'global', 'salutationW', 'Mrs');

        $this->removeLocalization('salutationGeneral');
    }
}
