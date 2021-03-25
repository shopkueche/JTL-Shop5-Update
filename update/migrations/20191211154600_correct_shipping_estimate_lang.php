<?php
/**
 * Correct shipping estimate lang
 *
 * @author mh
 * @created Wed, 9 Oct 2019 11:21:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191211154600
 */
class Migration_20191211154600 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Correct shipping estimate lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'estimateShippingCostsTo', 'Versandkostenermittlung für aktuellen Warenkorbinhalt nach');
        $this->setLocalization('eng', 'checkout', 'estimateShippingCostsTo', 'Determine shipping costs for current basket to');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'checkout', 'estimateShippingCostsTo', 'Versandkostenermittlung nach');
        $this->setLocalization('eng', 'checkout', 'estimateShippingCostsTo', 'Determine shipping costs according to');
    }
}
