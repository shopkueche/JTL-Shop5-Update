<?php
/**
 * add row in temailvorlagesprache for english version of Status Email
 *
 * @author dr
 * @created Thu, 03 Nov 2016 11:00:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161103110000
 */
class Migration_20161103110000 extends Migration implements IMigration
{
    protected $author = 'dr';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $cContentHtml = $this->getDB()->escape(file_get_contents(PFAD_ROOT . PFAD_EMAILVORLAGEN . 'eng/email_bericht_html.tpl'));
        $cContentText = $this->getDB()->escape(file_get_contents(PFAD_ROOT . PFAD_EMAILVORLAGEN . 'eng/email_bericht_plain.tpl'));
        $english      = $this->getDB()->select('tsprache', 'cIso', 'eng', null, null, null, null, false, 'kSprache');

        if ($english !== null) {
            $this->execute("
                INSERT INTO temailvorlagesprache
                    VALUES (
                        (SELECT kEmailvorlage FROM temailvorlage WHERE cModulId = 'core_jtl_statusemail'),
                        " . (int)$english->kSprache . ",
                        'Status email', '" . $cContentHtml . "', '" . $cContentText . "', '', ''
                    )
                    ON DUPLICATE KEY UPDATE
                        cBetreff = 'Status Email',
                        cContentHtml = '" . $cContentHtml . "',
                        cContentText = '" . $cContentText . "'
            ");
            $this->execute("
                INSERT INTO temailvorlagespracheoriginal
                    VALUES (
                        (SELECT kEmailvorlage FROM temailvorlage WHERE cModulId = 'core_jtl_statusemail'),
                        " . (int)$english->kSprache . ",
                        'Status email', '" . $cContentHtml . "', '" . $cContentText . "', '', ''
                    )
                    ON DUPLICATE KEY UPDATE
                        cBetreff = 'Status Email',
                        cContentHtml = '" . $cContentHtml . "',
                        cContentText = '" . $cContentText . "'
            ");
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $enlish = $this->getDB()->select('tsprache', 'cIso', 'eng', null, null, null, null, false, 'kSprache');

        if ($enlish !== null) {
            $this->execute("
                DELETE FROM temailvorlagesprache
                    WHERE kEmailvorlage = (SELECT kEmailvorlage 
                        FROM temailvorlage 
                        WHERE cModulId = 'core_jtl_statusemail'
                    ) AND kSprache = " . (int)$enlish->kSprache . '
            ');
            $this->execute("
                DELETE FROM temailvorlagespracheoriginal
                    WHERE kEmailvorlage = (SELECT kEmailvorlage 
                        FROM temailvorlage 
                        WHERE cModulId = 'core_jtl_statusemail'
                    ) AND kSprache = " . (int)$enlish->kSprache . '
            ');
        }
    }
}
