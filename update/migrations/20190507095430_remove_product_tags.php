<?php
/**
 * remove_product_tags
 *
 * @author mh
 * @created Tue, 07 May 2019 09:54:30 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190507095430
 */
class Migration_20190507095430 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove product tags';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('configgroup_110_tag_filter');
        $this->removeConfig('configgroup_5_product_tagging');
        $this->removeConfig('configgroup_8_box_tagcloud');
        $this->removeConfig('configgroup_10_tagging_overview');
        $this->removeConfig('allgemein_tagfilter_benutzen');
        $this->removeConfig('tagfilter_max_anzeige');
        $this->removeConfig('tag_filter_type');
        $this->removeConfig('tagging_freischaltung');
        $this->removeConfig('tagging_anzeigen');
        $this->removeConfig('tagging_max_count');
        $this->removeConfig('tagging_max_ip_count');
        $this->removeConfig('boxen_tagging_anzeigen');
        $this->removeConfig('boxen_tagging_count');
        $this->removeConfig('sonstiges_tagging_all_count');
        $this->removeConfig('sitemap_tags_anzeigen');

        //remove LINKTYP_TAGGING
        $this->execute('DELETE FROM `tspezialseite` WHERE `nLinkart` = 14');
        $this->execute("DELETE `tlink`, `tlinkgroupassociations`, `tseo`
                          FROM `tlink`
                          LEFT JOIN `tlinkgroupassociations`
                            ON tlink.kLink = tlinkgroupassociations.linkID 
                          LEFT JOIN `tseo`
                            ON tlink.kLink = tseo.kKey AND tseo.cKey = 'kLink'
                          WHERE tlink.nLinkart = 14"
        );
        //remove PAGE_TAGGING
        $this->execute('DELETE FROM `tboxensichtbar` WHERE `kSeite` = 22');
        $this->execute('DELETE FROM `tboxenanzeige` WHERE `nSeite` = 22');
        $this->execute('DELETE FROM `textensionpoint` WHERE `nSeite` = 22');
        //remove BOX_TAGWOLKE, BOX_FILTER_TAG
        $this->execute('DELETE `tboxvorlage`, `tboxen`, `tboxensichtbar`
                          FROM `tboxvorlage`
                          LEFT JOIN `tboxen`
                            ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                          LEFT JOIN `tboxensichtbar`
                            ON tboxen.kBox = tboxensichtbar.kBox
                          WHERE tboxvorlage.kBoxvorlage = 24 OR tboxvorlage.kBoxvorlage = 32'
        );

        $this->execute("DELETE FROM `tseo` WHERE `cKey` = 'kTag'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'configgroup_110_tag_filter',
            'Tagfilter',
            CONF_NAVIGATIONSFILTER,
            'Tagfilter',
            null,
            170,
            (object)['cConf' => 'N']
        );
        $this->setConfig(
            'configgroup_5_product_tagging',
            'Produkttagging',
            CONF_ARTIKELDETAILS,
            'Produkttagging',
            null,
            1000,
            (object)['cConf' => 'N']
        );
        $this->setConfig(
            'configgroup_8_box_tagcloud',
            'Tagwolke',
            CONF_BOXEN,
            'Tagwolke',
            null,
            1000,
            (object)['cConf' => 'N']
        );
        $this->setConfig(
            'configgroup_10_tagging_overview',
            'Tagging Übersicht',
            CONF_SONSTIGES,
            'Tagging Übersicht',
            null,
            100,
            (object)['cConf' => 'N']
        );
        $this->setConfig(
            'allgemein_tagfilter_benutzen',
            'Y',
            CONF_NAVIGATIONSFILTER,
            'Tagfilter benutzen',
            'selectbox',
            172,
            (object)[
                'cBeschreibung' => 'Soll die Tagfilterung beim Filtern benutzt werden?',
                'inputOptions'  => [
                    'content' => 'Ja, im Contentbereich',
                    'box'     => 'Ja, in Navigationsbox',
                    'Y'       => 'Ja, im Contentbereich und der Navigationsbox',
                    'N'       => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'tag_filter_type',
            'Y',
            CONF_NAVIGATIONSFILTER,
            'Typ des Tagfilters',
            'selectbox',
            176,
            (object)[
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A'       => 'Verundung',
                    'O'       => 'Veroderung'
                ]
            ]
        );
        $this->setConfig(
            'tagfilter_max_anzeige',
            'Y',
            CONF_NAVIGATIONSFILTER,
            'Maximale Anzahl an Tags in der Filterung',
            'number',
            175,
            (object)['cBeschreibung' => 'Wieviele Tags sollen maximal in der Filteranzeige zu sehen sein?']
        );
        $this->setConfig(
            'tagging_freischaltung',
            'Y',
            CONF_ARTIKELDETAILS,
            'Produkttags Eingabe anzeigen',
            'selectbox',
            1010,
            (object)[
                'cBeschreibung' => 'Produkttags von Besuchern können unter den Produkten angezeigt werden.',
                'inputOptions'  => [
                    'Y'       => 'Ja, nur für eingeloggte Kunden',
                    'O'       => 'Ja, für alle Besucher',
                    'N'       => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'tagging_anzeigen',
            'Y',
            CONF_ARTIKELDETAILS,
            'Produkttags beim Artikel anzeigen',
            'selectbox',
            1020,
            (object)[
                'cBeschreibung' => 'Hier wird die Anzeige der Produkttags beim Artikel aktiviert bzw. deaktiviert.',
                'inputOptions'  => [
                    'Y'       => 'Ja',
                    'N'       => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'tagging_max_count',
            'Y',
            CONF_ARTIKELDETAILS,
            'Anzahl angezeigter Produkttags',
            'number',
            1030,
            (object)['cBeschreibung' => 'Soviele Begriffe werden bei den Produkttags angezeigt.']
        );
        $this->setConfig(
            'tagging_max_ip_count',
            'Y',
            CONF_ARTIKELDETAILS,
            'Maximale Einträge pro Besucher und Tag',
            'number',
            1040,
            (object)['cBeschreibung' => 'Damit verhindern Sie, dass einzelne IPs das Tagging manipulieren.']
        );
        $this->setConfig(
            'boxen_tagging_anzeigen',
            'Y',
            CONF_BOXEN,
            'Box anzeigen',
            'selectbox',
            1005,
            (object)[
                'cBeschreibung' => 'Soll die Tagwolke in einer Box angezeigt werden?',
                'inputOptions'  => [
                    'Y'       => 'Ja',
                    'N'       => 'Nein'
                ],
                'nModul'        => 1
            ]
        );
        $this->setConfig(
            'boxen_tagging_count',
            'Y',
            CONF_BOXEN,
            'Anzahl angezeigte Tagbegriffe',
            'number',
            1010,
            (object)[
                'cBeschreibung' => 'Soviele Begriffe werden in der Tagwolke angezeigt.',
                'nModul'        => 1
            ]
        );
        $this->setConfig(
            'sonstiges_tagging_all_count',
            'Y',
            CONF_SONSTIGES,
            'Anzahl angezeigte Tagbegriffe in der Übersicht',
            'number',
            110,
            (object)[
                'cBeschreibung' => 'Soviele Begriffe werden in der Komplettübersicht der Tagwolke angezeigt',
                'nModul'        => 1
            ]
        );
        $this->setConfig(
            'sitemap_tags_anzeigen',
            'Y',
            CONF_SITEMAP,
            'Tags anzeigen',
            'selectbox',
            100,
            (object)[
                'cBeschreibung' => 'Sollen Ihre Tags in der Sitemap erscheinen?',
                'inputOptions'  => [
                    'Y'       => 'Ja',
                    'N'       => 'Nein'
                ]
            ]
        );
        $this->execute("INSERT INTO `tboxvorlage`   VALUES (24, 0, 'tpl', 'Filter (Tag)', '2', 'box_filter_tag.tpl')");
        $this->execute("INSERT INTO `tboxvorlage`   VALUES (32, 0, 'tpl', 'Tagwolke', '0', 'box_tag_cloud.tpl')");
        $this->execute("INSERT INTO `tspezialseite` VALUES (10,0,'Tagging Übersicht','',14,14)");
    }
}
