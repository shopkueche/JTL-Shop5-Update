<?php
/**
 * add_lang_var_wrong_bic
 *
 * @author mh
 * @created Mon, 24 Sep 2018 09:28:13 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180924092813
 */
class Migration_20180924092813 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang var wrongBic';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'wrongBIC', 'Die eingegebene BIC hat ein ungültiges Format');
        $this->setLocalization('eng', 'checkout', 'wrongBIC', 'The submitted BIC has an invalid format');

        $this->setConfig(
            'zahlungsart_lastschrift_kreditinstitut_abfrage',
            'O',
            CONF_ZAHLUNGSARTEN,
            'Kreditinstitut abfragen',
            'selectbox',
            590,
            (object)[
                'cBeschreibung' => 'Soll das Feld Kreditinstitut im Bestellvorgang abgefragt werden?',
                'cModulId'      => 'za_lastschrift_jtl',
                'inputOptions'  => [
                    'N' => 'Nicht abfragen',
                    'O' => 'Optional',
                    'Y' => 'Pflichtangabe'
                ],
            ],
            true
        );
        $this->removeConfig('zahlungsart_lastschrift_iban_abfrage');
        $this->removeConfig('zahlungsart_lastschrift_kontonummer_abfrage');
        $this->removeConfig('zahlungsart_lastschrift_blz_abfrage');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('wrongBIC');

        $this->setConfig(
            'zahlungsart_lastschrift_iban_abfrage',
            'O',
            CONF_ZAHLUNGSARTEN,
            'IBAN abfragen',
            'selectbox',
            550,
            (object)[
                'cBeschreibung' => 'Soll das Feld IBAN im Bestellvorgang abgefragt werden?',
                'cModulId' => 'za_lastschrift_jtl',
                'inputOptions'  => [
                    'O' => 'Optional',
                    'Y' => 'Pflichtangabe'
                ],
            ],
            true
        );
        $this->setConfig(
            'zahlungsart_lastschrift_kontonummer_abfrage',
            'Y',
            CONF_ZAHLUNGSARTEN,
            'Kontonummer abfragen',
            'selectbox',
            570,
            (object)[
                'cBeschreibung' => 'Soll das Feld Kontonummer im Bestellvorgang abgefragt werden?',
                'cModulId'      => 'za_lastschrift_jtl',
                'inputOptions'  => [
                    'N' => 'Nicht abfragen',
                    'O' => 'Optional',
                    'Y' => 'Pflichtangabe'
                ],
            ],
            true
        );
        $this->setConfig(
            'zahlungsart_lastschrift_blz_abfrage',
            'Y',
            CONF_ZAHLUNGSARTEN,
            'BLZ abfragen',
            'selectbox',
            580,
            (object)[
                'cBeschreibung' => 'Soll das Feld BLZ im Bestellvorgang abgefragt werden?',
                'cModulId'      => 'za_lastschrift_jtl',
                'inputOptions'  => [
                    'N' => 'Nicht abfragen',
                    'O' => 'Optional',
                    'Y' => 'Pflichtangabe'
                ],
            ],
            true
        );
        $this->removeConfig('zahlungsart_lastschrift_kreditinstitut_abfrage');
    }
}
