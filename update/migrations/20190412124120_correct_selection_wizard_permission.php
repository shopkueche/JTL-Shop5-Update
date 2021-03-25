<?php
/**
 * correct_selection_wizard_permission
 *
 * @author mh
 * @created Fri, 12 Apr 2019 12:41:20 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190412124120
 */
class Migration_20190412124120 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Correct selection wizard permission';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE `tadminrecht`
                SET cRecht='EXTENSION_SELECTIONWIZARD_VIEW'
                WHERE cRecht='EXTENSION_SELECTIONMWIZARD_VIEW'"
        );
        $this->execute(
            "UPDATE `tadminrechtegruppe`
                SET cRecht='EXTENSION_SELECTIONWIZARD_VIEW'
                WHERE cRecht='EXTENSION_SELECTIONMWIZARD_VIEW'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "UPDATE `tadminrecht`
                SET cRecht='EXTENSION_SELECTIONMWIZARD_VIEW'
                WHERE cRecht='EXTENSION_SELECTIONWIZARD_VIEW'"
        );
        $this->execute(
            "UPDATE `tadminrechtegruppe`
                SET cRecht='EXTENSION_SELECTIONMWIZARD_VIEW'
                WHERE cRecht='EXTENSION_SELECTIONWIZARD_VIEW'"
        );
    }
}
