<?php declare(strict_types=1);
/**
 * @author fm
 * @created Thu, 26 Sep 2019 13:03:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190926130300
 */
class Migration_20190926130300 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add more image naming options';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'bilder_hersteller_namen',
            '1',
            CONF_BILDER,
            'Bildnamen von Herstellerbildern:',
            'selectbox',
            542,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, wie der Name der Bilddateien für Hersteller erzeugt werden soll.',
                'inputOptions'  => [
                    '0' => 'Primärschlüssel (Vorgabe von JTL-Wawi)',
                    '1' => 'Kategoriename (URL-Pfad)',
                    '2' => 'Dateiname aus JTL-Wawi'
                ]
            ]
        );
        $this->setConfig(
            'bilder_merkmal_namen',
            '1',
            CONF_BILDER,
            'Bildnamen von Merkmalbildern:',
            'selectbox',
            544,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, wie der Name der Bilddateien für Merkmale erzeugt werden soll.',
                'inputOptions'  => [
                    '0' => 'Primärschlüssel (Vorgabe von JTL-Wawi)',
                    '1' => 'Merkmalname',
                    '2' => 'Dateiname aus JTL-Wawi'
                ]
            ]
        );
        $this->setConfig(
            'bilder_merkmalwert_namen',
            '1',
            CONF_BILDER,
            'Bildnamen von Merkmalwertbildern:',
            'selectbox',
            546,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, wie der Name der Bilddateien für Merkmalwerte erzeugt werden soll.',
                'inputOptions'  => [
                    '0' => 'Primärschlüssel (Vorgabe von JTL-Wawi)',
                    '1' => 'Merkmalwertname (URL-Pfad)',
                    '2' => 'Dateiname aus JTL-Wawi'
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('bilder_hersteller_namen');
        $this->removeConfig('bilder_merkmal_namen');
        $this->removeConfig('bilder_merkmalwert_namen');
    }
}
