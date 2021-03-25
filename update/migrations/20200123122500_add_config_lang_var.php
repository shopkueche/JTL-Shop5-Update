<?php

/**
 * adds config lang var
 *
 * @author ms
 * @created Tue, 23 Jan 2020 12:25:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200123122500
 */
class Migration_20200123122500 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds config lang var';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'applyConfiguration', 'Konfiguration übernehmen');
        $this->setLocalization('eng', 'productDetails', 'applyConfiguration', 'apply configuration');

        $this->setLocalization('ger', 'productDetails', 'saveConfiguration', 'Speichern');
        $this->setLocalization('eng', 'productDetails', 'saveConfiguration', 'Save');

        $this->setLocalization('ger', 'productDetails', 'finishConfiguration', 'Konfiguration abschließen');
        $this->setLocalization('eng', 'productDetails', 'finishConfiguration', 'Finish configuration');

        $this->setLocalization('ger', 'productDetails', 'nextConfigurationGroup', 'Nächste Konfigurationsgruppe');
        $this->setLocalization('eng', 'productDetails', 'nextConfigurationGroup', 'Next configuration group');

        $this->setLocalization('ger', 'productDetails', 'configChooseNumberComponents', 'Bitte wählen Sie genau %d Komponenten.');
        $this->setLocalization('eng', 'productDetails', 'configChooseNumberComponents', 'Please select %d components.');

        $this->setLocalization('ger', 'productDetails', 'configChooseMinMaxComponents', 'Bitte wählen Sie mindestens %d und maximal %d.');
        $this->setLocalization('eng', 'productDetails', 'configChooseMinMaxComponents', 'Please select at least %d and a maximum of %d.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('applyConfiguration');
        $this->removeLocalization('saveConfiguration');
        $this->removeLocalization('finishConfiguration');
        $this->removeLocalization('nextConfigurationGroup');
        $this->removeLocalization('configChooseNumberComponents');
        $this->removeLocalization('configChooseMinMaxComponents');
    }
}
