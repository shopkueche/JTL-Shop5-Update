<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Change List Portlet class name
 *
 * @author dr
 */

class Migration_20190130123800 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Change List Portlet class name';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE topcportlet SET cClass = 'ListPortlet' WHERE cClass = 'PList'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE topcportlet SET cClass = 'PList' WHERE cClass = 'ListPortlet'");
    }
}
