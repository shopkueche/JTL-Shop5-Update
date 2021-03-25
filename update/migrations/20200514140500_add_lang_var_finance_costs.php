<?php declare(strict_types=1);

/**
 * @author ms
 * @created Thu, 14 May 2020 14:05:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200514140500
 */
class Migration_20200514140500 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'Add lang var for finance costs';

    /**
     * @inheritDoc
     */
    public function up()
    {

        $this->setLocalization('ger', 'order', 'financeCosts', 'zzgl. Finanzierungskosten');
        $this->setLocalization('eng', 'order', 'financeCosts', 'plus finance costs');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('financeCosts', 'order');
    }
}
