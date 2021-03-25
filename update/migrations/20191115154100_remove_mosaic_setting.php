<?php
/**
 * Remove mosaic setting
 *
 * @author mh
 * @created Fri, 15 Nov 2019 15:41:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191115154100
 */
class Migration_20191115154100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove mosaic setting';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'products_per_page_gallery',
            '10,20,30,40,50',
            CONF_ARTIKELUEBERSICHT,
            'Auswahloptionen Artikel pro Seite in Gallerieansicht',
            'text',
            855,
            (object)[
                'cBeschreibung' => 'Mit Komma getrennt, -1 für alle',
            ],
            true
        );
        $this->setConfig(
            'artikeluebersicht_anzahl_darstellung1',
            '10',
            CONF_ARTIKELUEBERSICHT,
            'Anzahl Artikel in Listendarstellung',
            'number',
            840,
            (object)[
                'cBeschreibung' => 'Wieviele Artikel sollen in der Listendarstellung auf einmal in der ' .
                    'Artikelansicht angezeigt werden?',
            ],
            true
        );
        $this->setConfig(
            'artikeluebersicht_anzahl_darstellung2',
            '20',
            CONF_ARTIKELUEBERSICHT,
            'Anzahl Artikel in Galeriedarstellung',
            'number',
            850,
            (object)[
                'cBeschreibung' => 'Wieviele Artikel sollen in der Galeriedarstellung auf einmal in der ' .
                    'Artikelansicht angezeigt werden?',
            ],
            true
        );
        $this->setConfig(
            'artikeluebersicht_erw_darstellung_stdansicht',
            '2',
            CONF_ARTIKELUEBERSICHT,
            'Standard-Darstellung für Artikelübersicht',
            'selectbox',
            835,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, welche Ansichtsoption der Artikelübersicht Ihren Kunden ' .
                    'standardmäßig angezeigt wird.',
                'inputOptions'  => [
                    '1' => 'Liste',
                    '2' => 'Galerie'
                ],
            ],
            true
        );
        $this->removeConfig('artikeluebersicht_anzahl_darstellung3');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'products_per_page_gallery',
            '9,12,15,18,21',
            CONF_ARTIKELUEBERSICHT,
            'Auswahloptionen Artikel pro Seite in Gallerieansicht',
            'text',
            855,
            (object)[
                'cBeschreibung' => 'Mit Komma getrennt, -1 für alle',
            ],
            true
        );
        $this->setConfig(
            'artikeluebersicht_erw_darstellung_stdansicht',
            '2',
            CONF_ARTIKELUEBERSICHT,
            'Standard-Darstellung für Artikelübersicht',
            'selectbox',
            835,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, welche Ansichtsoption der Artikelübersicht Ihren Kunden ' .
                    'standardmäßig angezeigt wird. Mit dem Standard-EVO-Template von JTL-Shop können Sie nur die ' .
                    'Optionen Galerie und Liste verwenden.',
                'inputOptions'  => [
                    '1' => 'Liste',
                    '2' => 'Galerie',
                    '3' => 'Mosaik'
                ],
            ],
            true
        );
        $this->setConfig(
            'artikeluebersicht_anzahl_darstellung3',
            '40',
            CONF_ARTIKELUEBERSICHT,
            'Artikelanzahl in Mosaikdarstellung',
            'number',
            860,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, wie viele Artikel jeweils in der Mosaikdarstellung ' .
                    'angezeigt werden.',
            ]
        );
    }
}
