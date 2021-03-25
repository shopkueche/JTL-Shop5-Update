<?php
/**
 * Alter tzahlungsinfo to represent sync status
 *
 * @author fp
 * @created Mon, 27 Feb 2017 10:04:40 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170227100440
 */
class Migration_20170227100440 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Alter tzahlungsinfo to represent sync status';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "ALTER TABLE tzahlungsinfo
                ADD COLUMN cAbgeholt VARCHAR(1) NOT NULL DEFAULT 'N'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE tzahlungsinfo
                DROP COLUMN cAbgeholt'
        );
    }
}
