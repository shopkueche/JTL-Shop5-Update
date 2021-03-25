<?php
/**
 * adds poll error message
 *
 * @author ms
 * @created Mon, 19 Dec 2016 13:00:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161219130000
 */
class Migration_20161219130000 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'messages', 'pollError', 'Bei der Auswertung ist ein Fehler aufgetreten.');
        $this->setLocalization('eng', 'messages', 'pollError', 'An error occured during validation.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('pollError');
    }
}
