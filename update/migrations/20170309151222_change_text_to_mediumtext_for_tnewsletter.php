<?php
/**
 * Change text to mediumtext for tnewsletter
 *
 * @author fp
 * @created Thu, 09 Mar 2017 15:12:22 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170309151222
 */
class Migration_20170309151222 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Change text to mediumtext for tnewsletter';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE tnewsletter
                CHANGE COLUMN cInhaltHTML cInhaltHTML MEDIUMTEXT NOT NULL,
                CHANGE COLUMN cInhaltText cInhaltText MEDIUMTEXT NOT NULL'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE tnewsletter
                CHANGE COLUMN cInhaltHTML cInhaltHTML TEXT NOT NULL,
                CHANGE COLUMN cInhaltText cInhaltText TEXT NOT NULL'
        );
    }
}
