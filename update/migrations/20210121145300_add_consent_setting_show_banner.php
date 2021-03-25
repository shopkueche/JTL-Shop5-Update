<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210121145300
 */
class Migration_20210121145300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add consent setting show banner';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'consent_manager_show_banner',
            '1',
            CONF_CONSENTMANAGER,
            'Banner bei x-tem Seitenaufruf anzeigen',
            'selectbox',
            110,
            (object)[
                'cBeschreibung' => 'Legen Sie hier fest, ob der Banner zum Erteilen oder Ablehnen einer globalen' .
                    ' Einwilligung dem Besucher sofort angezeigt werden soll oder ob der Besucher erst auf der' .
                    ' zweiten oder dritten Seite mit dem Banner konfrontiert werden soll.',
                'inputOptions'  => [
                    '1' => 'Beim ersten Seitenaufruf (1)',
                    '2' => 'Beim zweiten Seitenaufruf (2)',
                    '3' => 'Beim dritten Seitenaufruf (3)',
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('consent_manager_show_banner');
    }
}
