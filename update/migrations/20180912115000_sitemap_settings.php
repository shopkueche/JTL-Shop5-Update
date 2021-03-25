<?php
/**
 * Sitemap settings
 *
 * @author fm
 * @created Wed, 12 Sep 2018 11:50:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180912115000
 */
class Migration_20180912115000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Sitemap settings';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->setConfig(
            'sitemap_images_categories',
            'N',
            CONF_SITEMAP,
            'Kategoriebilder anzeigen',
            'selectbox',
            121,
            (object)[
                'cBeschreibung' => 'Sollen Kategoriebilder mit in die Sitemap aufgenommen werden?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ],
            true
        );
        $this->setConfig(
            'sitemap_images_manufacturers',
            'N',
            CONF_SITEMAP,
            'Herstellerbilder anzeigen',
            'selectbox',
            122,
            (object)[
                'cBeschreibung' => 'Sollen Herstellerbilder mit in die Sitemap aufgenommen werden?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ],
            true
        );
        $this->setConfig(
            'sitemap_images_newscategory_items',
            'N',
            CONF_SITEMAP,
            'Newskategoriebilder anzeigen',
            'selectbox',
            123,
            (object)[
                'cBeschreibung' => 'Sollen Newskategoriebilder mit in die Sitemap aufgenommen werden?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ],
            true
        );
        $this->setConfig(
            'sitemap_images_news_items',
            'N',
            CONF_SITEMAP,
            'Newsbeitragsbilder anzeigen',
            'selectbox',
            124,
            (object)[
                'cBeschreibung' => 'Sollen Newsbeitragsbilder mit in die Sitemap aufgenommen werden?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ],
            true
        );
        $this->setConfig(
            'sitemap_images_attributes',
            'N',
            CONF_SITEMAP,
            'Merkmalbilder anzeigen',
            'selectbox',
            125,
            (object)[
                'cBeschreibung' => 'Sollen Merkmalbilder mit in die Sitemap aufgenommen werden?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('sitemap_images_categories');
        $this->removeConfig('sitemap_images_manufacturers');
        $this->removeConfig('sitemap_images_newscategory_items');
        $this->removeConfig('sitemap_images_news_items');
        $this->removeConfig('sitemap_images_attributes');
    }
}
