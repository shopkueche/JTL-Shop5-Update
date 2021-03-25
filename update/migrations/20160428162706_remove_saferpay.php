<?php
/**
 * remove_saferpay
 *
 * @author wp
 * @created Thu, 28 Apr 2016 16:27:06 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160428162706
 */
class Migration_20160428162706 extends Migration implements IMigration
{
    protected $author = 'wp';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE `tzahlungsart` SET `nActive` = 0, `nNutzbar` = 0 WHERE `cModulId` = 'za_saferpay_jtl'");
        $this->execute(
            "DELETE FROM `tversandartzahlungsart` 
                WHERE `kZahlungsart` IN 
                      (SELECT `kZahlungsart` FROM `tzahlungsart` WHERE `cModulId` = 'za_saferpay_jtl')"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE `tzahlungsart` SET `nActive` = 1, `nNutzbar` = 1 WHERE `cModulId` = 'za_saferpay_jtl'");
    }
}
