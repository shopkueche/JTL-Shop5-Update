<?php
/**
 * Update admin bootstrap template in database
 *
 * @author msc
 * @created Mon, 27 Aug 2018 09:11:16 +0200
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180827091116
 */
class Migration_20180827091116 extends Migration implements IMigration
{
    protected $author      = 'msc';
    protected $description = 'Update admin bootstrap template in database';

    /**
     * @inheritDoc
     */
    public function up()
    {
        // Moved to Migration_20180801124135
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
