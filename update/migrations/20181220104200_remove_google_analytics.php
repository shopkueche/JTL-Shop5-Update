<?php
/**
 * remove_google_analytics
 *
 * @author mh
 * @created Thu, 20 Dec 2018 10:42:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181220104200
 */
class Migration_20181220104200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'remove Google Analytics';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('global_google_analytics_id');
        $this->removeConfig('global_google_ecommerce');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'global_google_analytics_id',
            '',
            CONF_GLOBAL,
            'Google Analytics ID',
            'text',
            520,
            (object)[
                'cBeschreibung' => 'Falls Sie einen Google Analytics Account haben, ' .
                    'tragen Sie hier Ihre ID ein (z.B. UA-xxxxxxx-x)'
            ]
        );
        $this->setConfig(
            'global_google_ecommerce',
            0,
            CONF_GLOBAL,
            'Google Analytics eCommerce Erweiterung nutzen',
            'selectbox',
            520,
            (object)[
                'cBeschreibung' => 'M&ouml;chten Sie, dass Google alle Ihre Verk&auml;ufe trackt?',
                'inputOptions'  => [
                    0 => 'Nein',
                    1 => 'Ja'
                ]
            ]
        );
    }
}
