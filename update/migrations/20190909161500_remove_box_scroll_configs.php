<?php
/**
 * Remove box scroll configs
 *
 * @author mh
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190909161500
 */
class Migration_20190909161500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove box scroll configs';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('box_bestseller_scrollen');
        $this->removeConfig('box_sonderangebote_scrollen');
        $this->removeConfig('box_neuimsortiment_scrollen');
        $this->removeConfig('box_topangebot_scrollen');
        $this->removeConfig('box_erscheinende_scrollen');
        $this->removeConfig('boxen_topbewertet_scrollbar');
        $this->removeConfig('boxen_preisradar_scrollbar');

        $this->removeConfig('boxen_preisradar_anzahl');
        $this->removeConfig('boxen_preisradar_anzahltage');
        $this->removeConfig('configgroup_8_box_priceradar');

        $this->setConfig(
            'box_erscheinende_anzahl_basis',
            '10',
            CONF_BOXEN,
            'Basisanzahl Produkte',
            'number',
            830,
            (object)[
                'cBeschreibung' => 'Menge, aus der die anzuzeigenden Produkte zufällig ausgesucht werden sollen'
            ],
            true
        );
        $this->setConfig(
            'box_sonderangebote_anzahl_basis',
            '10',
            CONF_BOXEN,
            'Basisanzahl Produkte',
            'number',
            230,
            (object)[
                'cBeschreibung' => 'Menge, aus der die anzuzeigenden Produkte zufällig ausgesucht werden sollen'
            ],
            true
        );
        $this->setConfig(
            'box_neuimsortiment_anzahl_basis',
            '10',
            CONF_BOXEN,
            'Basisanzahl Produkte',
            'number',
            325,
            (object)[
                'cBeschreibung' => 'Menge, aus der die anzuzeigenden Produkte zufällig ausgesucht werden sollen'
            ],
            true
        );
        $this->setConfig(
            'box_topangebot_anzahl_basis',
            '10',
            CONF_BOXEN,
            'Basisanzahl Produkte',
            'number',
            430,
            (object)[
                'cBeschreibung' => 'Menge, aus der die anzuzeigenden Produkte zufällig ausgesucht werden sollen'
            ],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'box_bestseller_scrollen',
            '0',
            CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            115,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'box_sonderangebote_scrollen',
            '0',
            CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            215,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'box_neuimsortiment_scrollen',
            '0',
            CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            315,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'box_topangebot_scrollen',
            '0',
            CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            415,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'box_erscheinende_scrollen',
            '0',
            CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            815,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'boxen_topbewertet_scrollbar',
            '0',
            CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            1320,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'boxen_preisradar_scrollbar',
            '0',
            CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            1410,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );

        $this->setConfig(
            'boxen_preisradar_anzahl',
            '3',
            CONF_BOXEN,
            'Anzahl Artikel anzeigen',
            'number',
            1420,
            (object)[
                'cBeschreibung' => 'Wieviele Artikel sollen gleichzeitig in der Box zu sehen sein?'
            ],
            true
        );
        $this->setConfig(
            'boxen_preisradar_anzahltage',
            '30',
            CONF_BOXEN,
            'Wieviele Tage sollen beachtet werden?',
            'number',
            1430,
            (object)[
                'cBeschreibung' => 'Wieviele Tage in der Vergangenheit, sollen für den Preisverlauf beachtet werden?'
            ],
            true
        );
        $this->setConfig(
            'configgroup_8_box_priceradar',
            'Box: Preisradar',
            CONF_BOXEN,
            'Box: Preisradar',
            null,
            1400,
            (object)['cConf' => 'N']
        );

        $this->removeConfig('box_erscheinende_anzahl_basis');
        $this->removeConfig('box_sonderangebote_anzahl_basis');
        $this->removeConfig('box_neuimsortiment_anzahl_basis');
        $this->removeConfig('box_topangebot_anzahl_basis');
    }
}
