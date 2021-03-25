<?php
/**
 * remove global attributes
 *
 * @author mh
 * @created Tue, 11 June 2019 11:37:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190611113700
 */
class Migration_20190611113700 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove global attributes data';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'DELETE `tboxvorlage`, `tboxen`, `tboxensichtbar`
                FROM `tboxvorlage`
                LEFT JOIN `tboxen`
                  ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                LEFT JOIN `tboxensichtbar`
                  ON tboxen.kBox = tboxensichtbar.kBox
                WHERE tboxvorlage.kBoxvorlage = 20'
        );
        $this->removeConfig('sitemap_globalemerkmale_anzeigen');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "INSERT INTO `tboxvorlage` VALUES (20, 0, 'tpl', 'Globale Merkmale', '0', 'box_characteristics_global.tpl')"
        );
        $this->setConfig(
            'sitemap_globalemerkmale_anzeigen',
            'Y',
            CONF_SITEMAP,
            'Globale Merkmale anzeigen',
            'selectbox',
            40,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, ob Seiten für globale Merkmale in der Sitemap erscheinen sollen.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
    }
}
