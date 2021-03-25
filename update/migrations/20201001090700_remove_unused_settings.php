<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201001090700
 */
class Migration_20201001090700 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove unused skalieren settings';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->removeConfig('bilder_kategorien_skalieren');
        $this->removeConfig('bilder_variationen_gross_skalieren');
        $this->removeConfig('bilder_variationen_skalieren');
        $this->removeConfig('bilder_variationen_mini_skalieren');
        $this->removeConfig('bilder_artikel_gross_skalieren');
        $this->removeConfig('bilder_artikel_normal_skalieren');
        $this->removeConfig('bilder_artikel_klein_skalieren');
        $this->removeConfig('bilder_artikel_mini_skalieren');
        $this->removeConfig('bilder_hersteller_normal_skalieren');
        $this->removeConfig('bilder_hersteller_klein_skalieren');
        $this->removeConfig('bilder_merkmal_normal_skalieren');
        $this->removeConfig('bilder_merkmal_klein_skalieren');
        $this->removeConfig('bilder_merkmalwert_normal_skalieren');
        $this->removeConfig('bilder_merkmalwert_klein_skalieren');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setConfig(
            'bilder_kategorien_skalieren',
            'N',
            CONF_BILDER,
            'Kategoriebilder skalieren',
            'selectbox',
            109,
            (object)[
                'cBeschreibung' => 'Kategoriebilder skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_variationen_gross_skalieren',
            'N',
            CONF_BILDER,
            'Variationsbilder Größe skalieren',
            'selectbox',
            127,
            (object)[
                'cBeschreibung' => 'Variationsbilder Größe skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_variationen_skalieren',
            'N',
            CONF_BILDER,
            'Variationsbilder skalieren',
            'selectbox',
            130,
            (object)[
                'cBeschreibung' => 'Variationsbilder skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_variationen_mini_skalieren',
            'N',
            CONF_BILDER,
            'Produktbilder Größe skalieren',
            'selectbox',
            142,
            (object)[
                'cBeschreibung' => 'Produktbilder Größe skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_artikel_gross_skalieren',
            'N',
            CONF_BILDER,
            'Produktbilder Groß skalieren',
            'selectbox',
            149,
            (object)[
                'cBeschreibung' => 'Produktbilder Groß skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_artikel_normal_skalieren',
            'N',
            CONF_BILDER,
            'Produktbilder Normal skalieren',
            'selectbox',
            169,
            (object)[
                'cBeschreibung' => 'Produktbilder Normal skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_artikel_klein_skalieren',
            'N',
            CONF_BILDER,
            'Produktbilder Klein skalieren',
            'selectbox',
            189,
            (object)[
                'cBeschreibung' => 'Produktbilder Klein skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_artikel_mini_skalieren',
            'N',
            CONF_BILDER,
            'Produktbilder Mini skalieren',
            'selectbox',
            202,
            (object)[
                'cBeschreibung' => 'Produktbilder Mini skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_hersteller_normal_skalieren',
            'N',
            CONF_BILDER,
            'Herstellerbilder Normal skalieren',
            'selectbox',
            209,
            (object)[
                'cBeschreibung' => 'Herstellerbilder Normal skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_hersteller_klein_skalieren',
            'N',
            CONF_BILDER,
            'Herstellerbilder Klein skalieren',
            'selectbox',
            229,
            (object)[
                'cBeschreibung' => 'Herstellerbilder Klein skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_merkmal_normal_skalieren',
            'N',
            CONF_BILDER,
            'Merkmalbilder Normal skalieren',
            'selectbox',
            249,
            (object)[
                'cBeschreibung' => 'Merkmalbilder Normal skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_merkmal_klein_skalieren',
            'N',
            CONF_BILDER,
            'Merkmalbilder Klein skalieren',
            'selectbox',
            269,
            (object)[
                'cBeschreibung' => 'Merkmalbilder Klein skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_merkmalwert_normal_skalieren',
            'N',
            CONF_BILDER,
            'Merkmalwertbilder Normal skalieren',
            'selectbox',
            289,
            (object)[
                'cBeschreibung' => 'Merkmalwertbilder Normal skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'bilder_merkmalwert_klein_skalieren',
            'N',
            CONF_BILDER,
            'Merkmalwertbilder Klein skalieren',
            'selectbox',
            309,
            (object)[
                'cBeschreibung' => 'Merkmalwertbilder Klein skalieren',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
    }
}
