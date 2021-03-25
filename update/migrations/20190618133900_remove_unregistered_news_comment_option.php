<?php
/**
 * @author fm
 * @created Tue, 18 June 2019 13:39:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190618133900
 */
class Migration_20190618133900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Remove option to allow news comments for unregistered users';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('news_kommentare_eingeloggt');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'news_kommentare_eingeloggt',
            'N',
            CONF_NEWS,
            'Einloggen um Kommentare zu schreiben',
            'selectbox',
            70,
            (object)[
                'cBeschreibung' => 'Muss man als Besucher eingeloggt sein um einen Newskommentar zu schreiben ' .
                    'oder dürfen es alle Besucher?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
    }
}
