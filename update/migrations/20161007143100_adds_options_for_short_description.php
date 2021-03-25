<?php
/**
 * adds options for short description
 *
 * @author ms
 * @created Fri, 07 Oct 2016 14:31:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161007143100
 */
class Migration_20161007143100 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'artikeldetails_kurzbeschreibung_anzeigen',
            'Y',
            CONF_ARTIKELDETAILS,
            'Kurzbeschreibung anzeigen',
            'selectbox',
            365,
            (object)[
                'cBeschreibung' => 'Soll die Kurzbeschreibung des Artikels auf der Detailseite angezeigt werden?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'artikeluebersicht_kurzbeschreibung_anzeigen',
            'N',
            CONF_ARTIKELUEBERSICHT,
            'Kurzbeschreibung anzeigen',
            'selectbox',
            315,
            (object)[
                'cBeschreibung' => 'Soll die Kurzbeschreibung des Artikels auf &Uuml;bersichtsseiten angezeigt werden?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('artikeldetails_kurzbeschreibung_anzeigen');
        $this->removeConfig('artikeluebersicht_kurzbeschreibung_anzeigen');
    }
}
