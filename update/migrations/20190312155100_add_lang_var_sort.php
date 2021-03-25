<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * add_lang_var_sort
 *
 * @author ms
 * @created Tue, 12 Mar 2019 15:51:00 +0100
 */

/**
 * Class Migration_20190312155100
 */
class Migration_20190312155100 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'Add lang var for sort';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'filterAndSort', 'Filter & Sortierung');
        $this->setLocalization('eng', 'global', 'filterAndSort', 'filters & sorting');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('filterAndSort');
    }
}
