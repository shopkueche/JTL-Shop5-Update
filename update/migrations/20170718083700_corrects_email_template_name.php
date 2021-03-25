<?php
/**
 * corrects email template name
 *
 * @author ms
 * @created Tue, 18 Jul 2017 08:37:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170718083700
 */
class Migration_20170718083700 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'corrects email template name';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE temailvorlage SET cName='Warenrücksendung abgeschickt' WHERE cModulId='core_jtl_rma_submitted' AND cDateiname ='rma'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE temailvorlage SET cName='Warenrücksendung abegeschickt' WHERE cModulId='core_jtl_rma_submitted' AND cDateiname ='rma'");
    }
}
