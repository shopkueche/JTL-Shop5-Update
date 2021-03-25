<?php
/**
 * @author fm
 * @created Wed, 03 Apr 2019 15:31:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\DB\ReturnType;

/**
 * Class Migration_20190403153100
 */
class Migration_20190403153100 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'remove old payment methods';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $methods = $this->getDB()->query(
            "SELECT * 
                FROM tzahlungsart 
                WHERE cModulId LIKE 'za_billpay%_jtl' 
                    OR cModulId = 'za_sofortueberweisung_jtl'
                    OR cModulId = 'za_wirecard_jtl'",
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($methods as $method) {
            $id = (int)$method->kZahlungsart;
            $this->getDB()->delete('tzahlungsartsprache', 'kZahlungsart', $id);
            $this->getDB()->delete('tzahlungsart', 'kZahlungsart', $id);
            $this->getDB()->delete('tversandartzahlungsart', 'kZahlungsart', $id);
        }
        $this->execute("DELETE FROM tadminrecht WHERE cRecht = 'ORDER_BILLPAY_VIEW'");
        $this->execute("DELETE FROM tadminrechtegruppe WHERE cRecht = 'ORDER_BILLPAY_VIEW'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'sofortueberweisungDesc'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName LIKE 'fundingAdvice%'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName LIKE 'safetypay%'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName LIKE 'saferpay%'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName LIKE 'paymentPartner%'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName LIKE 'heidelpay%'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName LIKE 'HPError-%'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'fromJust'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'checkoutPayment'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financingTotal'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financingRateTable'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financingRateFactor'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financingNotice'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financingHoldback'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financingIncludesProcessingFee'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financingDuration'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financingBorrowingRate'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financingAnnualRate'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'financing'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'postfinanceDesc'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'payWithHeidelpay'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'payWithClickpay'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'payWithPostfinance'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'payWithSofortueberweisung'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName LIKE 'uos%'");
        $this->execute("DELETE FROM tsprachwerte WHERE bSystem = 1 AND cName = 'wirecardText'");
        $this->execute("DELETE FROM tadminmenu WHERE cLinkname = 'Billpay' AND cRecht = 'ORDER_BILLPAY_VIEW'");
        $this->execute(
            "DELETE FROM teinstellungen 
                WHERE cModulId LIKE 'za_billpay%_jtl' 
                    OR cModulId = 'za_sofortueberweisung_jtl'
                    OR cModulId = 'za_wirecard_jtl'"
        );
        $this->execute(
            "DELETE FROM teinstellungenconf
                WHERE cModulId LIKE 'za_billpay%_jtl' 
                    OR cModulId = 'za_sofortueberweisung_jtl'
                    OR cModulId = 'za_wirecard_jtl'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "INSERT INTO `tadminrecht` 
                (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) 
                VALUES ('ORDER_BILLPAY_VIEW', 'Billpay', '5')"
        );
    }
}
