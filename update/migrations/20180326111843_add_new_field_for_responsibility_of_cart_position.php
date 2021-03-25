<?php
/**
 * Add new field for responsibilty of cart position.
 *
 * @author fp
 * @created Mon, 26 Mar 2018 11:18:43 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180326111843
 */
class Migration_20180326111843 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add new field for responsibility of cart position.';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "ALTER TABLE twarenkorbpos ADD COLUMN cResponsibility VARCHAR(255) NOT NULL DEFAULT 'core' AFTER cUnique"
        );
        $this->execute(
            "ALTER TABLE twarenkorbperspos ADD COLUMN cResponsibility VARCHAR(255) NOT NULL DEFAULT 'core' AFTER cUnique"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE twarenkorbpos DROP COLUMN cResponsibility'
        );
        $this->execute(
            'ALTER TABLE twarenkorbperspos DROP COLUMN cResponsibility'
        );
    }
}
