<?php
/**
 * Rename the settings-menu entries "Einstellungen" into proper names
 *
 * @author cr
 * @created Fri, 27 Oct 2017 11:16:17 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20171027111617
 */
class Migration_20171027111617 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Rename the settings-menu entries "Einstellungen" into proper names';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('UPDATE `teinstellungensektion` SET `cName` = "Formulareinstellungen" WHERE `cRecht` = "SETTINGS_CUSTOMERFORM_VIEW"');
        $this->execute('UPDATE `teinstellungensektion` SET `cName` = "Emaileinstellungen" WHERE `cRecht` = "SETTINGS_EMAILS_VIEW"');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('UPDATE `teinstellungensektion` SET `cName` = "Einstellungen" WHERE `cRecht` = "SETTINGS_CUSTOMERFORM_VIEW"');
        $this->execute('UPDATE `teinstellungensektion` SET `cName` = "Einstellungen" WHERE `cRecht` = "SETTINGS_EMAILS_VIEW"');
    }
}
