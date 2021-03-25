<?php
/**
 * Add answer column to tbewertung
 *
 * @author dr
 * @created Tue, 07 Mar 2017 17:00:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170307170000
 */
class Migration_20170307170000 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add answer column to tbewertung';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tbewertung ADD COLUMN cAntwort TEXT AFTER dDatum');
        $this->execute('ALTER TABLE tbewertung ADD COLUMN dAntwortDatum DATE AFTER cAntwort');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tbewertung', 'dAntwortDatum');
        $this->dropColumn('tbewertung', 'cAntwort');
    }
}
