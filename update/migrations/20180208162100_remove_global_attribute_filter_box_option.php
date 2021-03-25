<?php
/**
 * Remove global attribute filter box option
 *
 * @author fm
 * @created Thu, 08 Feb 2018 16:21:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180208162100
 */
class Migration_20180208162100 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Remove global attribute filter box option';

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'allgemein_globalmerkmalfilter_benutzen',
            'Y',
            CONF_NAVIGATIONSFILTER,
            'Globale Merkmalbox benutzen',
            'selectbox',
            110,
            (object)[
                'cBeschreibung' => 'Sollen die globalen Merkmale in einer Box angezeigt werden?',
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
    public function up()
    {
        $this->removeConfig('allgemein_globalmerkmalfilter_benutzen');
    }
}
