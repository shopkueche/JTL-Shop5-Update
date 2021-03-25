<?php
/**
 * @author fm
 * @created Mon, 14 Oct 2019 11:36:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Media\Image;

/**
 * Class Migration_20191014113600
 */
class Migration_20191014113600 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Add news image size options';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'bilder_news_mini_breite',
            '120',
            CONF_BILDER,
            'Newsbilder Mini Breite',
            'number',
            500
        );
        $this->setConfig(
            'bilder_news_mini_hoehe',
            '40',
            CONF_BILDER,
            'Newsbilder Mini Höhe',
            'number',
            501
        );
        $this->setConfig(
            'bilder_news_klein_breite',
            '600',
            CONF_BILDER,
            'Newsbilder Klein Breite',
            'number',
            502
        );
        $this->setConfig(
            'bilder_news_klein_hoehe',
            '200',
            CONF_BILDER,
            'Newsbilder Klein Höhe',
            'number',
            503
        );
        $this->setConfig(
            'bilder_news_normal_breite',
            '1200',
            CONF_BILDER,
            'Newsbilder Normal Breite',
            'number',
            504
        );
        $this->setConfig(
            'bilder_news_normal_hoehe',
            '400',
            CONF_BILDER,
            'Newsbilder Normal Höhe',
            'number',
            505
        );
        $this->setConfig(
            'bilder_news_gross_breite',
            '1800',
            CONF_BILDER,
            'Newsbilder Groß Breite',
            'number',
            506
        );
        $this->setConfig(
            'bilder_news_gross_hoehe',
            '600',
            CONF_BILDER,
            'Newsbilder Groß Höhe',
            'number',
            507
        );
        $this->setConfig(
            'bilder_newskategorie_mini_breite',
            '120',
            CONF_BILDER,
            'Newskategoriebilder Mini Breite',
            'number',
            500
        );
        $this->setConfig(
            'bilder_newskategorie_mini_hoehe',
            '40',
            CONF_BILDER,
            'Newskategoriebilder Mini Höhe',
            'number',
            501
        );
        $this->setConfig(
            'bilder_newskategorie_klein_breite',
            '600',
            CONF_BILDER,
            'Newskategoriebilder Klein Breite',
            'number',
            502
        );
        $this->setConfig(
            'bilder_newskategorie_klein_hoehe',
            '200',
            CONF_BILDER,
            'Newskategoriebilder Klein Höhe',
            'number',
            503
        );
        $this->setConfig(
            'bilder_newskategorie_normal_breite',
            '1200',
            CONF_BILDER,
            'Newskategoriebilder Normal Breite',
            'number',
            504
        );
        $this->setConfig(
            'bilder_newskategorie_normal_hoehe',
            '400',
            CONF_BILDER,
            'Newskategoriebilder Normal Höhe',
            'number',
            505
        );
        $this->setConfig(
            'bilder_newskategorie_gross_breite',
            '1800',
            CONF_BILDER,
            'Newskategoriebilder Groß Breite',
            'number',
            506
        );
        $this->setConfig(
            'bilder_newskategorie_gross_hoehe',
            '600',
            CONF_BILDER,
            'Newskategoriebilder Groß Höhe',
            'number',
            507
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('bilder_news_mini_breite');
        $this->removeConfig('bilder_news_mini_hoehe');
        $this->removeConfig('bilder_news_klein_breite');
        $this->removeConfig('bilder_news_klein_hoehe');
        $this->removeConfig('bilder_news_normal_breite');
        $this->removeConfig('bilder_news_normal_hoehe');
        $this->removeConfig('bilder_news_gross_breite');
        $this->removeConfig('bilder_news_gross_hoehe');
        $this->removeConfig('bilder_newskategorie_mini_breite');
        $this->removeConfig('bilder_newskategorie_mini_hoehe');
        $this->removeConfig('bilder_newskategorie_klein_breite');
        $this->removeConfig('bilder_newskategorie_klein_hoehe');
        $this->removeConfig('bilder_newskategorie_normal_hoehe');
        $this->removeConfig('bilder_newskategorie_normal_breite');
        $this->removeConfig('bilder_newskategorie_gross_breite');
        $this->removeConfig('bilder_newskategorie_gross_hoehe');
    }
}
