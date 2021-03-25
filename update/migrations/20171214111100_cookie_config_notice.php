<?php
/**
 * @author fm
 * @created Thu, 11 Dec 2017 11:11:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20171214111100
 */
class Migration_20171214111100 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add cookie config notice';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE teinstellungenconf SET cName = 'Cookie-Einstellungen (Achtung: nur ändern, wenn Sie genau wissen, was Sie tun!)' WHERE cName = 'Cookie-Einstellungen'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE teinstellungenconf SET cName = 'Cookie-Einstellungen' WHERE cName = 'Cookie-Einstellungen (Achtung: nur ändern, wenn Sie genau wissen, was Sie tun!)'");
    }
}
