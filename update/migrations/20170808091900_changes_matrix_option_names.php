<?php
/**
 * changes matrix option names in configuration
 *
 * @author ms
 * @created Tue, 08 Aug 2017 09:19:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170808091900
 */
class Migration_20170808091900 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'changes matrix option names in configuration';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE teinstellungenconfwerte SET cName='Hochformat (nur bis zu 2 Variationen möglich)' WHERE kEinstellungenConf = 1330 AND cWert = 'H'");
        $this->execute("UPDATE teinstellungenconfwerte SET cName='Querformat (nur bis zu 2 Variationen möglich)' WHERE kEinstellungenConf = 1330 AND cWert = 'Q'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE teinstellungenconfwerte SET cName='Hochformat (nur bei 1 Variation möglich)' WHERE kEinstellungenConf = 1330 AND cWert = 'H'");
        $this->execute("UPDATE teinstellungenconfwerte SET cName='Querformat (nur bei 1 Variation möglich)' WHERE kEinstellungenConf = 1330 AND cWert = 'Q'");
    }
}
