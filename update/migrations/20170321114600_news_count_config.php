<?php
/**
 * Add news count config in news overview
 *
 * @author dr
 * @created Tue, 21 Mar 2017 11:46:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170321114600
 */
class Migration_20170321114600 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add news count config in news overview';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'news_anzahl_uebersicht',
            '10',
            113,
            'Anzahl News in der Übersicht',
            'number',
            30,
            (object)[
                'cBeschreibung' =>
                    'Wieviele News sollen standardmäßig in der Newsübersicht angezeigt werden? 0 = standard'
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('news_anzahl_uebersicht');
    }
}
