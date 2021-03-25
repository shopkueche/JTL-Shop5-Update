<?php


use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190425100000
 */
class Migration_20190425100000 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds language variables for product ribbons';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productOverview', 'ribbon-1', 'Bestseller');
        $this->setLocalization('eng', 'productOverview', 'ribbon-1', 'bestseller');
        $this->setLocalization('ger', 'productOverview', 'ribbon-2', 'Sale %s');
        $this->setLocalization('eng', 'productOverview', 'ribbon-2', 'sale %s');
        $this->setLocalization('ger', 'productOverview', 'ribbon-3', 'Neu');
        $this->setLocalization('eng', 'productOverview', 'ribbon-3', 'new');
        $this->setLocalization('ger', 'productOverview', 'ribbon-4', 'Top');
        $this->setLocalization('eng', 'productOverview', 'ribbon-4', 'top');
        $this->setLocalization('ger', 'productOverview', 'ribbon-5', 'bald verfügbar');
        $this->setLocalization('eng', 'productOverview', 'ribbon-5', 'coming soon');
        $this->setLocalization('ger', 'productOverview', 'ribbon-6', 'Top bewertet');
        $this->setLocalization('eng', 'productOverview', 'ribbon-6', 'top rated');
        $this->setLocalization('ger', 'productOverview', 'ribbon-7', 'Ausverkauft');
        $this->setLocalization('eng', 'productOverview', 'ribbon-7', 'sold out');
        $this->setLocalization('ger', 'productOverview', 'ribbon-8', 'Auf Lager');
        $this->setLocalization('eng', 'productOverview', 'ribbon-8', 'in stock');
        $this->setLocalization('ger', 'productOverview', 'ribbon-9', 'vorbestellen');
        $this->setLocalization('eng', 'productOverview', 'ribbon-9', 'pre order');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('ribbon-1');
        $this->removeLocalization('ribbon-2');
        $this->removeLocalization('ribbon-3');
        $this->removeLocalization('ribbon-4');
        $this->removeLocalization('ribbon-5');
        $this->removeLocalization('ribbon-6');
        $this->removeLocalization('ribbon-7');
        $this->removeLocalization('ribbon-8');
        $this->removeLocalization('ribbon-9');
    }
}
