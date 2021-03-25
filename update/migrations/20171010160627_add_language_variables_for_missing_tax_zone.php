<?php
/**
 * Add language variables for missing tax zone
 *
 * @author fp
 * @created Tue, 10 Oct 2017 16:06:27 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20171010160627
 */
class Migration_20171010160627 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add language variables for missing tax zone';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'errorMessages', 'missingTaxZoneForDeliveryCountry', 'Ein Versand nach %s ist aktuell nicht m&ouml;glich, da keine g&uuml;ltige Steuerzone hinterlegt ist.');
        $this->setLocalization('eng', 'errorMessages', 'missingTaxZoneForDeliveryCountry', 'A shipment to %s is currently not possible because there is no assigned tax zone.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('missingTaxZoneForDeliveryCountry');
    }
}
