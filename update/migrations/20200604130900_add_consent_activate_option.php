<?php declare(strict_types=1);

/**
 * @author mh
 * @created Mon, 05 Jun 2020 13:09:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200604130900
 */
class Migration_20200604130900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add consent activate option';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'consent_manager_active',
            'Y',
            CONF_CONSENTMANAGER,
            'Consent Manager aktivieren',
            'selectbox',
            100,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, ob der Consent Manager genutzt werden soll.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('consent_manager_active');
    }
}
