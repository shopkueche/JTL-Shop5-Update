<?php
/**
 * Add nova lang vars
 *
 * @author mh
 * @created Wed, 9 Oct 2019 11:21:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191009112100
 */
class Migration_20191009112100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add nova lang vars';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'selectChoose', 'Auswählen');
        $this->setLocalization('eng', 'global', 'selectChoose', 'Choose');
        $this->setLocalization('ger', 'global', 'warehouseAvailability', 'Bestand pro Lager anzeigen');
        $this->setLocalization('eng', 'global', 'warehouseAvailability', 'Show stock level per warehouse');
        $this->setLocalization('ger', 'global', 'warehouse', 'Lager');
        $this->setLocalization('eng', 'global', 'warehouse', 'Warehouse');
        $this->setLocalization('ger', 'global', 'status', 'Status');
        $this->setLocalization('eng', 'global', 'status', 'Status');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('selectChoose');
        $this->removeLocalization('warehouseAvailability');
        $this->removeLocalization('status');
        $this->removeLocalization('warehouse');
    }
}
