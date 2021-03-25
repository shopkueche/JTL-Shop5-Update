<?php
/**
 * Add product filter config
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180109100600
 */
class Migration_20180109100600 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add product filter config';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'tag_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Tagfilters',
            'selectbox',
            176,
            (object)[
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'Verundung',
                    'O' => 'Veroderung'
                ]
            ]
        );
        $this->setConfig(
            'category_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Kategoriefilters',
            'selectbox',
            148,
            (object)[
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'Verundung',
                    'O' => 'Veroderung'
                ]
            ]
        );
        $this->setConfig(
            'manufacturer_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Herstellerfilters',
            'selectbox',
            121,
            (object)[
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'Verundung',
                    'O' => 'Veroderung'
                ]
            ]
        );
        $this->setConfig(
            'search_special_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Suchspezialfilters',
            'selectbox',
            141,
            (object)[
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'Verundung',
                    'O' => 'Veroderung'
                ]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('tag_filter_type');
        $this->removeConfig('category_filter_type');
        $this->removeConfig('manufacturer_filter_type');
        $this->removeConfig('search_special_filter_type');
    }
}
