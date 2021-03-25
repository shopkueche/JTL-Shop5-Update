<?php
/**
 * @author mh
 * @created Mo, 30 September 2019 16:28:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190930162800
 */
class Migration_20190930162800 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove financial proposal config';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('artikeluebersicht_finanzierung_anzeigen');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'artikeluebersicht_finanzierung_anzeigen',
            'N',
            CONF_ARTIKELUEBERSICHT,
            'Finanzierungsvorschlag anzeigen',
            'selectbox',
            480,
            (object)[
                'cBeschreibung' => 'Wollen Sie das in der Artikelübersicht bei jedem Artikel ein' .
                    'Finanzierungsvorschlag angezeigt wird?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein'
                ]
            ]
        );
    }
}
