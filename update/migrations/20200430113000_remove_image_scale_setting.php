<?php
/**
 * Remove image scale setting
 *
 * @author mh
 * @created Thu, 30 Apr 2020 12:30:00 +0100
 */

use JTL\Boxes\Admin\BoxAdmin;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200430113000
 */
class Migration_20200430113000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove image scale setting';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('bilder_skalieren');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'bilder_skalieren',
            'N',
            CONF_BILDER,
            'Bilder hochskalieren?',
            'selectbox',
            580,
            (object)[
                'cBeschreibung' => 'Zu kleine Bilder werden automatisch hochskaliert',
                'inputOptions'  => [
                    'Y'       => 'Ja',
                    'N'       => 'Nein'
                ]
            ]
        );
    }
}
