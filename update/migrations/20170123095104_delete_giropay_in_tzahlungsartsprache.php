<?php
/**
 * delete giropay in tzahlungsartsprache
 *
 * @author msc
 * @created Mon, 23 Jan 2017 09:51:04 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170123095104
 */
class Migration_20170123095104 extends Migration implements IMigration
{
    protected $author = 'msc';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DELETE FROM `tzahlungsartsprache` WHERE `kZahlungsart` = 0');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Not necessary
    }
}
