<?php
/**
 * implement fallback-payment
 *
 * @author cr
 * @created Thu, 03 May 2018 09:56:34 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180503095634
 */
class Migration_20180503095634 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'implement fallback-payment';

    protected $szPaymentModuleId = 'za_null_jtl';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('INSERT INTO `tzahlungsart`(`kZahlungsart`, `cName`, `cModulId`, `cKundengruppen`, `cBild`, `nMailSenden`, `cAnbieter`, `cTSCode`, `nWaehrendBestellung`)
            VALUES(0, "Keine Zahlung erforderlich", "' . $this->szPaymentModuleId . '", "", "", 1, "", "", 0)');
        $oPaymentEntry = $this->fetchOne('SELECT * FROM `tzahlungsart` WHERE `cModulId` = "' . $this->szPaymentModuleId . '"');

        $this->execute('INSERT INTO `tzahlungsartsprache`(`kZahlungsart`, `cISOSprache`, `cName`, `cGebuehrname`, `cHinweisText`, `cHinweisTextShop`)
            VALUES(' . $oPaymentEntry->kZahlungsart . ', "ger", "Keine Zahlung erforderlich", "Keine Zahlung erforderlich", "Es ist keine Zahlung erforderlich. Ihr Shop-Guthaben wurde entsprechend verrechenet.",
            "Es ist keine Zahlung erforderlich. Ihr Shop-Guthaben wurde entsprechend verrechenet.")');
        $this->execute('INSERT INTO `tzahlungsartsprache`(`kZahlungsart`, `cISOSprache`, `cName`, `cGebuehrname`, `cHinweisText`, `cHinweisTextShop`)
            VALUES(' . $oPaymentEntry->kZahlungsart . ', "eng", "No payment needed", "No payment needed", "There is no further payment needed. Your shop-credit was billed.",
            "There is no further payment needed. Your shop-credit was billed.")');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $oPaymentEntry = $this->fetchOne('SELECT * FROM `tzahlungsart` WHERE `cModulId` = "' . $this->szPaymentModuleId . '"');

        $this->execute('DELETE FROM `tzahlungsart` WHERE `cModulID` = "' . $this->szPaymentModuleId . '"');
        $this->execute('DELETE FROM `tzahlungsartsprache` WHERE `kZahlungsart` = ' . (int)$oPaymentEntry->kZahlungsart);
    }
}
