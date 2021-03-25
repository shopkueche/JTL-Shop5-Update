<?php

/**
 * adds setting for GTIN display
 *
 * @author ms
 * @created Fri, 14 Feb 2020 10:00:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200214100000
 */
class Migration_20200214100000 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds setting for GTIN display';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'gtin_display',
            'always',
            CONF_ARTIKELDETAILS,
            'GTIN anzeigen',
            'selectbox',
            499,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, ob die GTIN angezeigt wird.',
                'inputOptions'  => [
                    'N'      => 'Nein',
                    'details' => 'Ja, auf der Artikeldetailseite',
                    'lists' => 'Ja, in Listen',
                    'always' => 'Ja, auf der Artikeldetailseite und in den Listen',
                ]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('gtin_display');
    }
}
