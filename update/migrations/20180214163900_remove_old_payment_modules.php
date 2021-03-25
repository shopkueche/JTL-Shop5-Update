<?php
/**
 * Remove old payment modules
 *
 * @author fm
 * @created Wed, 14 Feb 2018 16:39:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180214163900
 */
class Migration_20180214163900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Remove old payment modules';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("DELETE FROM teinstellungen WHERE cModulId LIKE 'za_mbqc_%_jtl' OR cModulId = 'za_dresdnercetelem_jtl' OR cModulId = 'za_clickandbuy_jtl'");
        $this->execute("DELETE FROM teinstellungenconf WHERE cModulId LIKE 'za_mbqc_%_jtl' OR cModulId = 'za_dresdnercetelem_jtl' OR cModulId = 'za_clickandbuy_jtl'");
        $this->execute("DELETE FROM tversandartzahlungsart WHERE kZahlungsart IN (SELECT kZahlungsart FROM tzahlungsart WHERE cModulId LIKE 'za_mbqc_%_jtl' OR cModulId = 'za_dresdnercetelem_jtl' OR cModulId = 'za_clickandbuy_jtl')");
        $this->execute("DELETE FROM tzahlungsartsprache WHERE kZahlungsart IN (SELECT kZahlungsart FROM tzahlungsart WHERE cModulId LIKE 'za_mbqc_%_jtl' OR cModulId = 'za_dresdnercetelem_jtl' OR cModulId = 'za_clickandbuy_jtl')");
        $this->execute("DELETE FROM tzahlungsart WHERE cModulId LIKE 'za_mbqc_%_jtl' OR cModulId = 'za_dresdnercetelem_jtl' OR cModulId = 'za_clickandbuy_jtl'");
        $this->execute('DELETE FROM tzahlungsartsprache WHERE kZahlungsart NOT IN (SELECT kZahlungsart FROM tzahlungsart)');
        $this->execute('DROP TABLE IF EXISTS tskrill');
        $this->execute("DELETE FROM tadminmenu WHERE cRecht = 'ORDER_SKRILL_VIEW' OR cRecht = 'ORDER_CLICKANDBUY_VIEW'");
        $this->execute("DELETE FROM tadminrecht WHERE cRecht = 'ORDER_SKRILL_VIEW' OR cRecht = 'ORDER_CLICKANDBUY_VIEW'");
        $this->execute("DELETE FROM tsprachwerte WHERE cName IN (
          'payWithMoneybookers', 'payWithMoneybookersQc', 'moneybookersDesc', 'moneybookersQcError', 
          'moneybookersQcHttpError', 'moneybookersQcText', 'moneybookersQCDesc', 
          'clickandbuy2hsError', 'desdnercetelemDesc', 'payWithDresdnercetelem')");
        $this->execute("DELETE FROM tsprachlog WHERE cName IN (
          'payWithMoneybookers', 'payWithMoneybookersQc', 'moneybookersDesc', 'moneybookersQcError', 
          'moneybookersQcHttpError', 'moneybookersQcText', 'moneybookersQCDesc', 
          'clickandbuy2hsError', 'desdnercetelemDesc', 'payWithDresdnercetelem')");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
