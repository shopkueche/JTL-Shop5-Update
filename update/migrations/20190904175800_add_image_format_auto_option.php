<?php
/**
 * @author fm
 * @created Wed, 4 Sep 2019 17:58:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Media\Image;

/**
 * Class Migration_20190904175800
 */
class Migration_20190904175800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add image extension auto detection option';

    /**
     * @var array
     */
    private static $types = [
        'Artikel'      => Image::TYPE_PRODUCT,
        'Kategorie'    => Image::TYPE_CATEGORY,
        'Variationen'  => Image::TYPE_VARIATION,
        'Hersteller'   => Image::TYPE_MANUFACTURER,
        'Merkmale'     => Image::TYPE_CHARACTERISTIC,
        'Merkmalwerte' => Image::TYPE_CHARACTERISTIC_VALUE
    ];

    /**
     * @var array
     */
    private static $positions = [
        'oben'         => 'top',
        'oben-rechts'  => 'top-right',
        'rechts'       => 'right',
        'unten-rechts' => 'bottom-right',
        'unten'        => 'bottom',
        'unten-links'  => 'bottom-left',
        'links'        => 'left',
        'oben-links'   => 'top-left',
        'zentriert'    => 'center'
    ];

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute("INSERT INTO teinstellungenconfwerte (`kEinstellungenConf`, `cName`, `cWert`, `nSort`)
            VALUES(1483, 'AUTO', 'AUTO', 2)");

        foreach (self::$types as $old => $new) {
            $this->execute("UPDATE `tbranding` SET `cBildKategorie` = '" .
                $new . "' WHERE `cBildKategorie` = '" . $old . "'");
        }
        foreach (self::$positions as $old => $new) {
            $this->execute("UPDATE `tbrandingeinstellung` SET `cPosition` = '" .
                $new . "' WHERE `cPosition` = '" . $old . "'");
        }

        $this->setConfig(
            'bilder_hersteller_mini_breite',
            '120',
            CONF_BILDER,
            'Herstellerbilder Mini Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_hersteller_mini_hoehe',
            '40',
            CONF_BILDER,
            'Herstellerbilder Mini Höhe',
            'number',
            100
        );
        $this->setConfig(
            'bilder_hersteller_gross_breite',
            '1800',
            CONF_BILDER,
            'Herstellerbilder Groß Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_hersteller_gross_hoehe',
            '600',
            CONF_BILDER,
            'Herstellerbilder Groß Höhe',
            'number',
            100
        );
        $this->setConfig(
            'bilder_merkmal_mini_breite',
            '120',
            CONF_BILDER,
            'Herstellerbilder Mini Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_merkmal_mini_hoehe',
            '40',
            CONF_BILDER,
            'Merkmalbilder Mini Höhe',
            'number',
            100
        );
        $this->setConfig(
            'bilder_merkmal_gross_breite',
            '1800',
            CONF_BILDER,
            'Merkmalbilder Groß Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_merkmal_gross_hoehe',
            '600',
            CONF_BILDER,
            'Merkmalbilder Groß Höhe',
            'number',
            100
        );

        $this->setConfig(
            'bilder_merkmalwert_mini_breite',
            '40',
            CONF_BILDER,
            'Merkmalwertbilder Mini Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_merkmalwert_mini_hoehe',
            '40',
            CONF_BILDER,
            'Merkmalwertbilder Mini Höhe',
            'number',
            100
        );
        $this->setConfig(
            'bilder_merkmalwert_gross_breite',
            '1800',
            CONF_BILDER,
            'Merkmalwertbilder Groß Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_merkmalwert_gross_hoehe',
            '600',
            CONF_BILDER,
            'Merkmalwertbilder Groß Höhe',
            'number',
            100
        );

        $this->setConfig(
            'bilder_konfiggruppe_mini_breite',
            '120',
            CONF_BILDER,
            'Konfiggruppenbilder Mini Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_konfiggruppe_mini_hoehe',
            '40',
            CONF_BILDER,
            'Konfiggruppenbilder Mini Höhe',
            'number',
            100
        );

        $this->setConfig(
            'bilder_konfiggruppe_normal_breite',
            '1200',
            CONF_BILDER,
            'Konfiggruppenbilder Normal Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_konfiggruppe_normal_hoehe',
            '400',
            CONF_BILDER,
            'Konfiggruppenbilder Normal Höhe',
            'number',
            100
        );
        $this->setConfig(
            'bilder_konfiggruppe_gross_breite',
            '1800',
            CONF_BILDER,
            'Konfiggruppenbilder Groß Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_konfiggruppe_gross_hoehe',
            '600',
            CONF_BILDER,
            'Konfiggruppenbilder Groß Höhe',
            'number',
            100
        );

        $this->setConfig(
            'bilder_kategorien_mini_breite',
            '120',
            CONF_BILDER,
            'Kategoriebilder Mini Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_kategorien_mini_hoehe',
            '40',
            CONF_BILDER,
            'Kategoriebilder Mini Höhe',
            'number',
            100
        );
        $this->setConfig(
            'bilder_kategorien_gross_breite',
            '1800',
            CONF_BILDER,
            'Kategoriebilder Groß Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_kategorien_gross_hoehe',
            '600',
            CONF_BILDER,
            'Kategoriebilder Groß Höhe',
            'number',
            100
        );
        $this->setConfig(
            'bilder_kategorien_klein_breite',
            '600',
            CONF_BILDER,
            'Kategoriebilder Klein Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_kategorien_klein_hoehe',
            '200',
            CONF_BILDER,
            'Kategoriebilder Klein Höhe',
            'number',
            100
        );
        $this->setConfig(
            'bilder_variationen_klein_breite',
            '200',
            CONF_BILDER,
            'Variationsbilder Klein Breite',
            'number',
            100
        );
        $this->setConfig(
            'bilder_variationen_klein_hoehe',
            '200',
            CONF_BILDER,
            'Variationsbilder Klein Höhe',
            'number',
            100
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute("DELETE FROM teinstellungenconfwerte WHERE `kEinstellungenConf` = 1483 AND `cName` = 'AUTO'");

        foreach (self::$types as $old => $new) {
            $this->execute("UPDATE `tbranding` SET `cBildKategorie` = '" . $old . "' WHERE `cBildKategorie` = '" . $new . "'");
        }
        foreach (self::$positions as $old => $new) {
            $this->execute("UPDATE `tbrandingeinstellung` SET `cPosition` = '" . $old . "' WHERE `cPosition` = '" . $new . "'");
        }

        $this->removeConfig('bilder_variationen_klein_hoehe');
        $this->removeConfig('bilder_variationen_klein_breite');
        $this->removeConfig('bilder_kategorien_klein_hoehe');
        $this->removeConfig('bilder_kategorien_klein_breite');
        $this->removeConfig('bilder_kategorien_gross_hoehe');
        $this->removeConfig('bilder_kategorien_gross_breite');
        $this->removeConfig('bilder_kategorien_mini_hoehe');
        $this->removeConfig('bilder_kategorien_mini_breite');
        $this->removeConfig('bilder_konfiggruppe_gross_hoehe');
        $this->removeConfig('bilder_konfiggruppe_gross_breite');
        $this->removeConfig('bilder_konfiggruppe_normal_hoehe');
        $this->removeConfig('bilder_konfiggruppe_normal_breite');
        $this->removeConfig('bilder_konfiggruppe_mini_hoehe');
        $this->removeConfig('bilder_konfiggruppe_mini_breite');
        $this->removeConfig('bilder_merkmalwert_gross_hoehe');
        $this->removeConfig('bilder_merkmalwert_gross_breite');
        $this->removeConfig('bilder_merkmalwert_mini_hoehe');
        $this->removeConfig('bilder_merkmalwert_mini_breite');
        $this->removeConfig('bilder_merkmal_gross_hoehe');
        $this->removeConfig('bilder_merkmal_gross_breite');
        $this->removeConfig('bilder_merkmal_mini_hoehe');
        $this->removeConfig('bilder_merkmal_mini_breite');
        $this->removeConfig('bilder_hersteller_gross_hoehe');
        $this->removeConfig('bilder_hersteller_gross_breite');
        $this->removeConfig('bilder_hersteller_mini_hoehe');
        $this->removeConfig('bilder_hersteller_mini_breite');
    }
}
