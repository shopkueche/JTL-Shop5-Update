<?php
/**
 * Add available column to redirect table
 *
 * @author dr
 * @created Tue, 09 May 2017 17:00:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170509165900
 */
class Migration_20170509165900 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add available column to redirect table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "ALTER TABLE tredirect
                ADD COLUMN cAvailable CHAR(1) DEFAULT 'u'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE tredirect
                DROP COLUMN bAvailable'
        );
    }
}
