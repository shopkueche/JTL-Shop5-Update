<?php
/**
 * add mail template file names to temailvorlageoriginal that where missing
 *
 * @author dr
 * @created Mon, 01 Nov 2016 15:28:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161101152800
 */
class Migration_20161101152800 extends Migration implements IMigration
{
    protected $author = 'dr';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("
            UPDATE temailvorlage
                SET cDateiname = 'email_bericht'
                WHERE cModulId = 'core_jtl_statusemail'
        ");
        $this->execute("
            UPDATE temailvorlageoriginal
                SET cDateiname = 'email_bericht'
                WHERE cModulId = 'core_jtl_statusemail'
        ");
        $this->execute("
            UPDATE temailvorlage
                SET cDateiname = 'checkbox_shopbetreiber'
                WHERE cModulId = 'core_jtl_checkbox_shopbetreiber'
        ");
        $this->execute("
            UPDATE temailvorlageoriginal
                SET cDateiname = 'checkbox_shopbetreiber'
                WHERE cModulId = 'core_jtl_checkbox_shopbetreiber'
        ");
        $this->execute("
            UPDATE temailvorlage
                SET cDateiname = 'admin_passwort_vergessen'
                WHERE cModulId = 'core_jtl_admin_passwort_vergessen'
        ");
        $this->execute("
            UPDATE temailvorlageoriginal
                SET cDateiname = 'admin_passwort_vergessen'
                WHERE cModulId = 'core_jtl_admin_passwort_vergessen'
        ");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("
            UPDATE temailvorlage
                SET cDateiname = ''
                WHERE cModulId = 'core_jtl_statusemail'
        ");
        $this->execute("
            UPDATE temailvorlageoriginal
                SET cDateiname = ''
                WHERE cModulId = 'core_jtl_statusemail'
        ");
        $this->execute("
            UPDATE temailvorlage
                SET cDateiname = ''
                WHERE cModulId = 'core_jtl_checkbox_shopbetreiber'
        ");
        $this->execute("
            UPDATE temailvorlageoriginal
                SET cDateiname = ''
                WHERE cModulId = 'core_jtl_checkbox_shopbetreiber'
        ");
        $this->execute("
            UPDATE temailvorlage
                SET cDateiname = ''
                WHERE cModulId = 'core_jtl_admin_passwort_vergessen'
        ");
        $this->execute("
            UPDATE temailvorlageoriginal
                SET cDateiname = ''
                WHERE cModulId = 'core_jtl_admin_passwort_vergessen'
        ");
    }
}
