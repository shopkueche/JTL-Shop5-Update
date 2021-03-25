<?php
/**
 * Remove survey data
 *
 * @author mh
 * @created Fri, 13 Dec 2019 08:33:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191213093300
 */
class Migration_20191213093300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove survey data';

    /**
     * @inheritDoc
     */
    public function up()
    {
        //remove LINKTYP_UMFRAGE
        $this->execute('DELETE FROM `tspezialseite` WHERE `nLinkart` = 22');
        $this->execute("DELETE `tlink`, `tlinkgroupassociations`, `tseo`
                          FROM `tlink`
                          LEFT JOIN `tlinkgroupassociations`
                            ON tlink.kLink = tlinkgroupassociations.linkID 
                          LEFT JOIN `tseo`
                            ON tlink.kLink = tseo.kKey AND tseo.cKey = 'kUmfrage'
                          WHERE tlink.nLinkart = 22"
        );
        //remove PAGE_UMFRAGE
        $this->execute('DELETE FROM `tboxensichtbar` WHERE `kSeite` = 6');
        $this->execute('DELETE FROM `tboxenanzeige` WHERE `nSeite` = 6');
        $this->execute('DELETE FROM `textensionpoint` WHERE `nSeite` = 6');
        //remove BOX_UMFRAGE
        $this->execute('DELETE `tboxvorlage`, `tboxen`, `tboxensichtbar`
                          FROM `tboxvorlage`
                          LEFT JOIN `tboxen`
                            ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                          LEFT JOIN `tboxensichtbar`
                            ON tboxen.kBox = tboxensichtbar.kBox
                          WHERE tboxvorlage.kBoxvorlage = 22'
        );

        $this->removeConfig('configgroup_115_poll');
        $this->removeConfig('umfrage_nutzen');
        $this->removeConfig('umfrage_einloggen');
        $this->removeConfig('umfrage_box_anzahl');

        $this->getDB()->delete('tseo', 'cKey', 'kUmfrage');

        $this->execute("DELETE FROM `tsprachsektion` WHERE cName = 'umfrage'");

        $this->removeLocalization('umfrage');
        $this->removeLocalization('umfrageBack');
        $this->removeLocalization('umfrageNext');
        $this->removeLocalization('umfrageQ');
        $this->removeLocalization('umfrageQNext');
        $this->removeLocalization('umfrageQPage');
        $this->removeLocalization('umfrageQRequired');
        $this->removeLocalization('umfrageSubmit');
        $this->removeLocalization('umfrageQs');

        $this->execute("DELETE FROM `tadminrecht` WHERE cRecht='EXTENSION_VOTE_VIEW'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("INSERT INTO `tboxvorlage`   VALUES (22, 0, 'tpl', 'Umfrage', '0', 'box_poll.tpl')");
        $this->execute("INSERT INTO `tspezialseite` VALUES (18,0,'Umfrage','umfrage.php',22,22)");

        $this->setConfig(
            'configgroup_115_poll',
            'Umfragesystem Einstellungen',
            115,
            'Umfragesystem Einstellungen',
            null,
            10,
            (object)['cConf' => 'N']
        );
        $this->setConfig(
            'umfrage_nutzen',
            'Y',
            115,
            'Umfragesystem benutzen',
            'selectbox',
            20,
            (object)[
                'cBeschreibung' => 'Wollen Sie das Umfragesystem im Shop nutzen?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'umfrage_einloggen',
            'y',
            30,
            'Muss man eingeloggt sein',
            'selectbox',
            30,
            (object)[
                'cBeschreibung' => 'Sollen sich Besucher vorher im Shop einloggen damit sie an einer Umfrage teilnehmen k?nnen?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'umfrage_box_anzahl',
            'N',
            115,
            'Anzahl Umfragen in der Box',
            'number',
            40,
            (object)[
                'cBeschreibung' => 'Wieviele Umfragen sollen in der Box angezeigt werden? Greift nur wenn die Option Box eingeschaltet ist.',
            ]
        );

        $this->execute("INSERT INTO `tsprachsektion` VALUES (15, 'umfrage')");

        $this->setLocalization('ger', 'umfrage', 'umfrage', 'Umfrage');
        $this->setLocalization('eng', 'umfrage', 'umfrage', 'Survey');
        $this->setLocalization('ger', 'umfrage', 'umfrageBack', 'Zurück');
        $this->setLocalization('eng', 'umfrage', 'umfrageBack', 'Back');
        $this->setLocalization('ger', 'umfrage', 'umfrageNext', 'Weiter');
        $this->setLocalization('eng', 'umfrage', 'umfrageNext', 'Next');
        $this->setLocalization('ger', 'umfrage', 'umfrageQ', 'Frage');
        $this->setLocalization('eng', 'umfrage', 'umfrageQ', 'Question');
        $this->setLocalization('ger', 'umfrage', 'umfrageQNext', 'Nächste Frage');
        $this->setLocalization('eng', 'umfrage', 'umfrageQNext', 'Next question');
        $this->setLocalization('ger', 'umfrage', 'umfrageQPage', 'Seite');
        $this->setLocalization('eng', 'umfrage', 'umfrageQPage', 'Page');
        $this->setLocalization('ger', 'umfrage', 'umfrageQRequired', 'Erforderliche Angabe');
        $this->setLocalization('eng', 'umfrage', 'umfrageQRequired', '(*) = Required information');
        $this->setLocalization('ger', 'umfrage', 'umfrageQs', 'Fragen');
        $this->setLocalization('eng', 'umfrage', 'umfrageQs', 'Questions');
        $this->setLocalization('ger', 'umfrage', 'umfrageSubmit', 'Umfrage absenden');
        $this->setLocalization('eng', 'umfrage', 'umfrageSubmit', 'Submit survey');

        $this->execute("INSERT INTO `tadminrecht` VALUES('EXTENSION_VOTE_VIEW', 'Umfragesystem')");
    }
}
