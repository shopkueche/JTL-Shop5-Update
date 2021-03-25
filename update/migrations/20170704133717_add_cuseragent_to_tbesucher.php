<?php
/**
 * Add cUserAgent to tBesucher
 *
 * @author fp
 * @created Tue, 04 Jul 2017 13:37:17 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170704133717
 */
class Migration_20170704133717 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add cUserAgent to tBesucher';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tbesucher ADD COLUMN cUserAgent VARCHAR(512) NULL AFTER cReferer');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tbesucher DROP COLUMN cUserAgent');
    }
}
