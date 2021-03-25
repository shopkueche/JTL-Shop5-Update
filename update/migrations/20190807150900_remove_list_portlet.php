<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Add default value for topcblueprint.kPlugin
 * Remove List Portlet
 *
 * @author dr
 */

class Migration_20190807150900 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Remove List Portlet';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("DELETE FROM topcportlet WHERE cClass = 'ListPortlet'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("
            INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup, bActive)
              VALUES (0, 'List', 'ListPortlet', 'layout', 1)
        ");
    }
}
