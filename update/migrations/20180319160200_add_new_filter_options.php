<?php
/**
 * Add boolean mode for fulltext search
 *
 * @author ms
 * @created Mon, 19 Mar 2018 16:02:00 +0100
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180319160200
 */
class Migration_20180319160200 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds options for new filters';

    /**
     * @inheritDoc
     */
    public function up()
    {
        /* Delete old filter boxes */
        $this->execute("DELETE FROM tboxvorlage WHERE cTemplate IN ('box_info.tpl', 'box_informationen.tpl')");
        $this->execute('DELETE FROM tboxvorlage WHERE (kBoxvorlage, cTemplate) IN (
                                                   (101, \'box_filter_manufacturer.tpl\'),
                                                   (102, \'box_filter_category.tpl\'))');
        /* Move custom boxes to another primary key */
        foreach ([101, 102] as $boxID) {
            $newBoxID = $this->getDB()->queryPrepared(
                'INSERT INTO `tboxvorlage` (kCustomID, eTyp, cName, cVerfuegbar, cTemplate)
                    SELECT kCustomID, eTyp, cName, cVerfuegbar, cTemplate
                      FROM `tboxvorlage`
                      WHERE kBoxvorlage = :boxID',
                ['boxID' => $boxID],
                ReturnType::LAST_INSERTED_ID
            );
            $this->getDB()->queryPrepared(
                'UPDATE `tboxen`
                  SET kBoxvorlage = :newBoxID
                  WHERE kBoxvorlage = :boxID',
                [
                    'newBoxID' => $newBoxID,
                    'boxID'    => $boxID,
                ],
                ReturnType::DEFAULT
            );
        }
        /* Remove boxes on reserved positions */
        $this->execute('DELETE FROM tboxen WHERE kBoxvorlage IN (101, 102)');
        $this->execute('DELETE FROM tboxvorlage WHERE kBoxvorlage IN (101, 102)');
        $this->execute(
            "INSERT INTO tboxvorlage 
                  (kBoxvorlage, kCustomID, eTyp, cName, cVerfuegbar, cTemplate) 
                VALUES (101, 0, 'tpl', 'Filter (Hersteller)', '2', 'box_filter_manufacturer.tpl'),
                       (102, 0, 'tpl', 'Filter (Kategorie)', '2', 'box_filter_category.tpl')"
        );

        $this->execute("UPDATE teinstellungenconf SET cName='Typ des Kategoriefilters' WHERE cWertName ='category_filter_type';");

        // Bewertungsfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='bewertungsfilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES (
                    (SELECT kEinstellungenConf
                        FROM teinstellungenconf
                        WHERE cWertName='bewertungsfilter_benutzen' LIMIT 1),
                    'Ja, im Contentbereich und der Navigationsbox',
                    'Y',
                    3
                )"
        );

        // Herstellerfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja, im Contentbereich und der Navigationsbox', nSort=3
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_herstellerfilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_herstellerfilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='allgemein_herstellerfilter_benutzen' LIMIT 1),
                        'Ja, im Contentbereich',
                        'content',
                        1),
                       (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='allgemein_herstellerfilter_benutzen' LIMIT 1),
                        'Ja, in Navigationsbox',
                        'box',
                        2)"
        );

        // Suchspecials - besondere Produkte
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja, im Contentbereich und der Navigationsbox', nSort=3
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_suchspecialfilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_suchspecialfilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='allgemein_suchspecialfilter_benutzen' LIMIT 1),
                        'Ja, im Contentbereich',
                        'content',
                        1),
                       (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='allgemein_suchspecialfilter_benutzen' LIMIT 1),
                        'Ja, in Navigationsbox',
                        'box',
                        2)"
        );

        // Kategoriefilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja, im Contentbereich und der Navigationsbox', nSort=3
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_kategoriefilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_kategoriefilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='allgemein_kategoriefilter_benutzen' LIMIT 1),
                        'Ja, im Contentbereich',
                        'content',
                        1),
                       (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='allgemein_kategoriefilter_benutzen' LIMIT 1),
                        'Ja, in Navigationsbox',
                        'box',
                        2)"
        );

        // Tagfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja, im Contentbereich und der Navigationsbox', nSort=3
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_tagfilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_tagfilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='allgemein_tagfilter_benutzen' LIMIT 1),
                        'Ja, im Contentbereich',
                        'content',
                        1),
                       (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='allgemein_tagfilter_benutzen' LIMIT 1),
                        'Ja, in Navigationsbox',
                        'box',
                        2)"
        );

        // Merkmalfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='merkmalfilter_verwenden' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='merkmalfilter_verwenden' LIMIT 1),
                        'Ja, im Contentbereich und der Navigationsbox',
                        'Y',
                        3)"
        );

        // Preisspannenfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=4
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='preisspannenfilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) 
                VALUES (
                        (SELECT kEinstellungenConf
                            FROM teinstellungenconf
                            WHERE cWertName='preisspannenfilter_benutzen' LIMIT 1),
                        'Ja, im Contentbereich und der Navigationsbox',
                        'Y',
                        3)"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "DELETE FROM tboxvorlage
                WHERE cTemplate='box_filter_manufacturer.tpl' OR cTemplate='box_filter_category.tpl'"
        );
        $this->execute(
            "DELETE FROM tboxen
                WHERE cTitel='Filter (Hersteller)' OR cTitel='Filter (Kategorie)'"
        );

        // Bewertungsfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=3
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='bewertungsfilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "DELETE FROM teinstellungenconfwerte  
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='bewertungsfilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );

        // Herstellerfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja', nSort=1
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_herstellerfilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=2
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_herstellerfilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_herstellerfilter_benutzen' LIMIT 1)
                  AND (cWert='box' OR cWert='content')"
        );

        // Suchspecials - besondere Produkte
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja', nSort=1
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_suchspecialfilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=2
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_suchspecialfilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_suchspecialfilter_benutzen' LIMIT 1)
                  AND (cWert='box' OR cWert='content')"
        );

        // Kategoriefilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja', nSort=1
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_kategoriefilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=2 WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_kategoriefilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_kategoriefilter_benutzen' LIMIT 1)
                  AND (cWert='box' OR cWert='content')"
        );

        // Tagfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET cName='Ja', nSort=1
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_tagfilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );

        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=2
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_tagfilter_benutzen' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='allgemein_tagfilter_benutzen' LIMIT 1)
                  AND (cWert='box' OR cWert='content')"
        );

        // Merkmalfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=3
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='merkmalfilter_verwenden' LIMIT 1)
                  AND cWert='N'"
        );

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='merkmalfilter_verwenden' LIMIT 1)
                  AND cWert='Y'"
        );

        // Preisspannenfilter
        $this->execute(
            "UPDATE teinstellungenconfwerte 
                SET nSort=3
                WHERE kEinstellungenConf = (
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='preisspannenfilter_benutzen' LIMIT 1)
                  AND cWert='N';"
        );

        $this->execute(
            "DELETE FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = ( 
                    SELECT kEinstellungenConf
                    FROM teinstellungenconf
                    WHERE cWertName='preisspannenfilter_benutzen' LIMIT 1)
                  AND cWert='Y'"
        );
    }
}
