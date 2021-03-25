<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;
/**
 * add_lang_var_config
 *
 * @author ms
 * @created Fri, 22 Mar 2019 13:51:00 +0100
 */

/**
 * Class Migration_20190322135100
 */
class Migration_20190322135100 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'Add lang var config';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'configChooseOneComponent',
            'Bitte wählen Sie genau eine Komponente');
        $this->setLocalization('eng', 'productDetails', 'configChooseOneComponent', 'Choose one component please');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('configChooseOneComponent');
    }
}
