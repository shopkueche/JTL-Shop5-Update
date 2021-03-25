<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190528085500
 */
class Migration_20190528085500 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Remove replace column from topcpage';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE topcpage DROP COLUMN bReplace');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE topcpage ADD COLUMN bReplace BOOL NOT NULL DEFAULT 0');
    }
}
