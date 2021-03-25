<?php

/**
 * adds child item bulk price setting
 *
 * @author ms
 * @created Fri, 24 Jan 2020 11:34:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200124113400
 */
class Migration_20200124113400 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds child item bulk price setting';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'general_child_item_bulk_pricing',
            'N',
            CONF_KAUFABWICKLUNG,
            'Variationsübergreifende Staffelpreise',
            'selectbox',
            280,
            (object)[
                'cBeschreibung' => 'Für Staffelpreisgrenzen im Warenkorb zählen alle Kindartikel einer VarKombi.',
                'inputOptions'  => [
                    'Y'      => 'Ja',
                    'N'      => 'Nein'
                ]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('general_child_item_bulk_pricing');
    }
}
