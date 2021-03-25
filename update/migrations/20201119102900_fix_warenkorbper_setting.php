<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201119102900
 */
class Migration_20201119102900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Fix warenkorbpers_nutzen setting';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->getDB()->update(
            'teinstellungenconf',
            'cWertName',
            'warenkorbpers_nutzen',
            (object)[
                'kEinstellungenSektion' => CONF_KAUFABWICKLUNG,
                'nSort'                 => 275,
                'nModul'                => 0
            ]
        );
        $this->getDB()->update(
            'teinstellungen',
            'cName',
            'warenkorbpers_nutzen',
            (object)['kEinstellungenSektion' => CONF_KAUFABWICKLUNG]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->getDB()->update(
            'teinstellungenconf',
            'cWertName',
            'warenkorbpers_nutzen',
            (object)[
                'kEinstellungenSektion' => CONF_GLOBAL,
                'nSort'                 => 810,
                'nModul'                => 1
            ]
        );
        $this->getDB()->update(
            'teinstellungen',
            'cName',
            'warenkorbpers_nutzen',
            (object)['kEinstellungenSektion' => CONF_GLOBAL]
        );
    }
}
