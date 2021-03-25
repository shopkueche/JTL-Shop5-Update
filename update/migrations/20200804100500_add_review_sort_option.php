<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200804100500
 */
class Migration_20200804100500 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add review sort option';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'bewertung_sortierung',
            0,
            CONF_BEWERTUNG,
            'Standard-Sortierung',
            'selectbox',
            125,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, wie Bewertungen standardmäßig sortiert werden.',
                'inputOptions'  => [
                    0 => 'Datum aufsteigend',
                    1 => 'Datum absteigend',
                    2 => 'Bewertung aufsteigend',
                    3 => 'Bewertung absteigend',
                    4 => 'Hilfreich aufsteigend',
                    5 => 'Hilfreich absteigend',
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('bewertung_sortierung');
    }
}
