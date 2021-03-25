<?php
/**
 * adds option for ken burns effect to sliders
 *
 * @author ms
 * @created Mon, 24 Oct 2016 12:41:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161024124100
 */
class Migration_20161024124100 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tslider ADD COLUMN bUseKB TINYINT(1) NOT NULL AFTER bRandomStart;');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tslider DROP COLUMN bUseKB');
    }
}
