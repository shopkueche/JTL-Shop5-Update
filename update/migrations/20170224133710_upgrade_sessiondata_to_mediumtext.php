<?php
/**
 * Upgrade sessiondata to MEDIUMTEXT
 *
 * @author fp
 * @created Fri, 24 Feb 2017 13:37:10 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170224133710
 */
class Migration_20170224133710 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Upgrade sessiondata to MEDIUMTEXT';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE tsession
                CHANGE COLUMN cSessionData cSessionData MEDIUMTEXT NULL DEFAULT NULL'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // In case of downgrade all sessions will be deleted to prevent invalid session data by truncating.
        $this->execute(
            'DELETE FROM tsession'
        );
        $this->execute(
            'ALTER TABLE tsession
                CHANGE COLUMN cSessionData cSessionData TEXT NULL DEFAULT NULL'
        );
    }
}
