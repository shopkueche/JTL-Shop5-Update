<?php
/**
 * generic double optin
 *
 * @author cr
 * @created Wed, 17 Apr 2019 12:38:59 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20190417123859 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'generic double optin';
    protected $kEmailvorlage;

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS toptin(
            kOptin int(10) NOT NULL AUTO_INCREMENT COMMENT 'internal table key',
            kOptinCode varchar(255) NOT NULL DEFAULT '' COMMENT 'main opt-in code',
            kOptinClass varchar(1024) DEFAULT '' COMMENT 'the class name of this optin',
            cMail varchar(256) NOT NULL DEFAULT '' COMMENT 'customer mail address',
            cRefData varchar(1024) DEFAULT NULL COMMENT 'additional reference data (e.g. text output in emails)',
            dCreated datetime DEFAULT NULL COMMENT 'opt-in created',
            dActivated datetime DEFAULT NULL COMMENT 'time of activation',
            PRIMARY KEY (kOptin),
            UNIQUE KEY (kOptinCode)
        )
        ENGINE = innodb
        DEFAULT CHARSET = utf8
        COLLATE = utf8_unicode_ci");
        $this->execute("CREATE TABLE IF NOT EXISTS toptinhistory(
            kOptinHistory int(10) NOT NULL AUTO_INCREMENT COMMENT 'internal table key',
            kOptinCode varchar(255) DEFAULT NULL COMMENT 'main OptInCode, derived from table toptin',
            kOptinClass varchar(1024) DEFAULT '' COMMENT 'the class name of this optin',
            cMail varchar(256) NOT NULL DEFAULT '' COMMENT 'customer mail address',
            cRefData varchar(1024) DEFAULT NULL COMMENT 'additional reference data (e.g. text output in emails)',
            dCreated datetime DEFAULT NULL COMMENT 'time of opt-in creation',
            dActivated datetime DEFAULT NULL COMMENT 'time of opt-in activation',
            dDeActivated datetime DEFAULT NULL COMMENT 'time of de-activation (moved to this place)',
            PRIMARY KEY (kOptinHistory),
            INDEX (kOptinCode)
        )
        ENGINE = innodb
        DEFAULT CHARSET = utf8
        COLLATE = utf8_unicode_ci");

        $this->setLocalization('ger', 'errorMessages', 'optinCodeUnknown', 'Der übergebene Bestätigungscode ist nicht bekannt.');
        $this->setLocalization('eng', 'errorMessages', 'optinCodeUnknown', 'The given confirmation code is unknown.');
        $this->setLocalization('ger', 'errorMessages', 'optinActionUnknown', 'Unbekannte Aktion angefordert.');
        $this->setLocalization('eng', 'errorMessages', 'optinActionUnknown', 'Unknown action requested.');
        //
        $this->setLocalization('ger', 'messages', 'availAgainOptinCreated',
          'Vielen Dank, Ihre Daten haben wir erhalten. Wir haben Ihnen eine E-Mail
           mit einem Freischaltcode zugeschickt.
           Bitte klicken Sie auf diesen Link in der E-Mail,
           um informiert zu werden, sobald der Artikel wieder verfügbar ist.');
        //
        $this->setLocalization('ger', 'messages', 'optinSucceded', 'Ihre Freischaltung ist erfolgt.');
        $this->setLocalization('eng', 'messages', 'optinSucceded', 'Your confirmation was successfull.');
        $this->setLocalization('ger', 'messages', 'optinSuccededAgain', 'Ihre Freischaltung ist bereits erfolgt.');
        $this->setLocalization('eng', 'messages', 'optinSuccededAgain', 'Your confirmation is already active.');
        $this->setLocalization('ger', 'messages', 'optinCanceled', 'Ihre Freischaltung wurde aufgehoben.');
        $this->setLocalization('eng', 'messages', 'optinCanceled', 'Your confirmation was canceled.');
        $this->setLocalization('ger', 'messages', 'optinRemoved', 'Ihr Freischaltantrag wurde entfernt.');
        $this->setLocalization('eng', 'messages', 'optinRemoved', 'Your activation request has been removed.');

        $this->execute("INSERT INTO temailvorlageoriginal(
                cName,
                cBeschreibung,
                cMailTyp,
                cModulId,
                cDateiname,
                cAktiv,
                nAKZ,
                nAGB,
                nWRB
            ) VALUE (
                'Benachrichtigung, wenn Produkt wieder verfügbar (Double Opt-in Anfrage)',
                '',
                'text/html',
                'core_jtl_verfuegbarkeitsbenachrichtigung_optin',
                'produkt_wieder_verfuegbar_optin',
                'Y',
                0,
                0,
                0
            )"
        );
        $this->execute("INSERT INTO temailvorlage(
                cName,
                cBeschreibung,
                cMailTyp,
                cModulId,
                cDateiname,
                cAktiv,
                nAKZ,
                nAGB,
                nWRB
            ) VALUE (
                'Benachrichtigung, wenn Produkt wieder verfügbar (Double Opt-in Anfrage)',
                '',
                'text/html',
                'core_jtl_verfuegbarkeitsbenachrichtigung_optin',
                'produkt_wieder_verfuegbar_optin',
                'Y',
                0,
                0,
                0
            )"
        );
        $this->kEmailvorlage = $this->fetchOne('SELECT last_insert_id() AS last_insert_id')->last_insert_id;

        // inserting the contents of the new file
        $optin_cContentHtml_de = addslashes(
            file_get_contents(PFAD_ROOT . 'admin/mailtemplates/ger/produkt_wieder_verfuegbar_optin_html.tpl')
        );
        $optin_cContentText_de = addslashes(
            file_get_contents(PFAD_ROOT . 'admin/mailtemplates/ger/produkt_wieder_verfuegbar_optin_plain.tpl')
        );
        $optin_cContentHtml_en = addslashes(
            file_get_contents(PFAD_ROOT . 'admin/mailtemplates/eng/produkt_wieder_verfuegbar_optin_html.tpl')
        );
        $optin_cContentText_en = addslashes(
            file_get_contents(PFAD_ROOT . 'admin/mailtemplates/eng/produkt_wieder_verfuegbar_optin_plain.tpl')
        );

        $this->execute('INSERT INTO temailvorlagespracheoriginal(
                kEmailvorlage,
                kSprache,
                cBetreff,
                cContentHtml,
                cContentText
            ) VALUES (
                ' . $this->kEmailvorlage . ",
                1,
                'Bestätigung für Produktinformation: #artikel.name#',
                '" . $optin_cContentHtml_de . "',
                '" . $optin_cContentText_de . "'
            ), (
                " . $this->kEmailvorlage . ",
                2,
                'Confirmation for product information: #artikel.name#',
                '" . $optin_cContentHtml_en . "',
                '" . $optin_cContentText_en . "'
            )
        ");

        $this->execute('INSERT INTO temailvorlagesprache(
                kEmailvorlage,
                kSprache,
                cBetreff,
                cContentHtml,
                cContentText
            ) VALUES (
                ' . $this->kEmailvorlage . ",
                1,
                'Bestätigung für Produktinformation: #artikel.name#',
                '" . $optin_cContentHtml_de . "',
                '" . $optin_cContentText_de . "'
            ), (
                " . $this->kEmailvorlage . ",
                2,
                'Confirmation for product information: #artikel.name#',
                '" . $optin_cContentHtml_en . "',
                '" . $optin_cContentText_en . "'
            )
        ");

        // update current availablity mail templates (according to the updated files)
        $kEmailvorlageProductAvailable = $this->fetchOne(
            "SELECT kEmailvorlage FROM temailvorlage where cModulId = 'core_jtl_verfuegbarkeitsbenachrichtigung'"
        )->kEmailvorlage;

        $cContentHtml_de = addslashes(
            file_get_contents(PFAD_ROOT . 'admin/mailtemplates/ger/produkt_wieder_verfuegbar_html.tpl')
        );
        $cContentText_de = addslashes(
            file_get_contents(PFAD_ROOT . 'admin/mailtemplates/ger/produkt_wieder_verfuegbar_plain.tpl')
        );
        $cContentHtml_en = addslashes(
            file_get_contents(PFAD_ROOT . 'admin/mailtemplates/eng/produkt_wieder_verfuegbar_html.tpl')
        );
        $cContentText_en = addslashes(
            file_get_contents(PFAD_ROOT . 'admin/mailtemplates/eng/produkt_wieder_verfuegbar_plain.tpl')
        );

        $this->execute("UPDATE temailvorlagespracheoriginal
            SET
                cContentHtml = '" . $cContentHtml_de . "',
                cContentText = '" . $cContentText_de . "' " .
            'WHERE
                kEmailvorlage = ' . $kEmailvorlageProductAvailable . ' ' .
                'AND kSprache = 1');

        $this->execute("UPDATE temailvorlagespracheoriginal
            SET
                cContentHtml = '" . $cContentHtml_en . "',
                cContentText = '" . $cContentText_en . "' " .
            'WHERE
                kEmailvorlage = ' . $kEmailvorlageProductAvailable . ' ' .
            'AND kSprache = 2');

        $this->execute("UPDATE temailvorlagesprache
            SET
                cContentHtml = '" . $cContentHtml_de . "',
                cContentText = '" . $cContentText_de. "' " .
            'WHERE
                kEmailvorlage = ' . $kEmailvorlageProductAvailable . ' ' .
            'AND kSprache = 1');

        $this->execute("UPDATE temailvorlagesprache
            SET
                cContentHtml = '" . $cContentHtml_en . "',
                cContentText = '" . $cContentText_en . "' " .
            'WHERE
                kEmailvorlage = ' . $kEmailvorlageProductAvailable  . ' ' .
            'AND kSprache = 2');

        $this->execute("ALTER TABLE temailvorlage MODIFY cDateiname
            varchar(255) DEFAULT '' NOT NULL COMMENT 'base file name in admin/mailtemplates/[ger|eng]/'");
        $this->execute("ALTER TABLE temailvorlage MODIFY cModulId
            varchar(255) COMMENT 'constant in includes/defines_inc.php'");
        $this->execute("ALTER TABLE temailvorlage MODIFY cBeschreibung
            mediumtext COMMENT 'for internal use'");
        $this->execute("ALTER TABLE temailvorlage MODIFY cName
            varchar(255) COMMENT 'is displayed in the backend'");
        $this->execute("ALTER TABLE temailvorlage MODIFY nDSE
            tinyint(3) NOT NULL DEFAULT 0 COMMENT 'append privatcy statement'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->kEmailvorlage = $this->fetchOne(
            "SELECT kEmailvorlage FROM temailvorlage where cModulId = 'core_jtl_verfuegbarkeitsbenachrichtigung_optin'"
        )->kEmailvorlage;

        $this->execute('DELETE FROM temailvorlagespracheoriginal WHERE kEmailvorlage = ' . $this->kEmailvorlage);
        $this->execute('DELETE FROM temailvorlagesprache WHERE kEmailvorlage = ' . $this->kEmailvorlage);

        $this->execute('DELETE FROM temailvorlageoriginal WHERE kEmailvorlage = ' . $this->kEmailvorlage);
        $this->execute('DELETE FROM temailvorlage WHERE kEmailvorlage = ' . $this->kEmailvorlage);

        $this->removeLocalization('optinRemoved');
        $this->removeLocalization('optinCanceled');
        $this->removeLocalization('optinSuccededAgain');
        $this->removeLocalization('optinSucceded');
        //
        $this->removeLocalization('availAgainOptinCreated');
        $this->removeLocalization('optinActionUnknown');
        $this->removeLocalization('optinCodeUnknown');

        $this->execute('DROP TABLE toptin');
        $this->execute('DROP TABLE toptinhistory');
    }
}
