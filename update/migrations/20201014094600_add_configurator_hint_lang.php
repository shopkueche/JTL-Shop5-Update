<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201014094600
 */
class Migration_20201014094600 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add configurator hint lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'productDetails',
            'completeConfigGroupHint',
            'Bitte wählen Sie zuerst Ihre gewünschten Komponenten in der aktuellen Gruppe aus. Klicken Sie dann auf „Nächste Konfigurationsgruppe“.'
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'completeConfigGroupHint',
            'Please select the desired components in the current group first. Then click \"Next configuration group\".'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('completeConfigGroupHint', 'productDetails');
    }
}
