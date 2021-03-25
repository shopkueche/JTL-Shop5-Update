<?php
/**
 * Convert encrypted data to utf-8
 *
 * @author fp
 * @created Tue, 09 Jan 2018 10:46:08 +0100
 */

use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180109104608
 */
class Migration_20180109104608 extends Migration implements IMigration
{
    protected $author = 'fp';

    protected $description = 'Convert encrypted data to utf-8';

    protected $properties = [
        'tkunde'            => ['kKunde', 'cNachname', 'cFirma', 'cZusatz', 'cStrasse'],
        'tzahlungsinfo'     => [
            'kZahlungsInfo',
            'cBankName',
            'cKartenNr',
            'cCVV',
            'cKontoNr',
            'cBLZ',
            'cIBAN',
            'cBIC',
            'cInhaber',
            'cVerwendungszweck'
        ],
        'tkundenkontodaten' => ['kKundenKontodaten', 'cBankName', 'nKonto', 'cBLZ', 'cIBAN', 'cBIC', 'cInhaber'],
    ];

    /**
     * @inheritDoc
     */
    public function up()
    {
        $cryptoService = Shop::Container()->getCryptoService();
        foreach ($this->properties as $tableName => $propNames) {
            $keyName = array_shift($propNames);
            $dataSet = $this->fetchAll(
                "SELECT $keyName, " . implode(', ', $propNames) .
                "   FROM $tableName"
            );

            foreach ($dataSet as $dataObj) {
                foreach ($propNames as $propName) {
                    if ($dataObj->$propName === null) {
                        continue;
                    }
                    $dataObj->$propName = $cryptoService->decryptXTEA($dataObj->$propName);
                    if (!Text::is_utf8($dataObj->$propName)) {
                        $dataObj->$propName = Text::convertUTF8($dataObj->$propName);
                    }
                    $dataObj->$propName = $cryptoService->encryptXTEA($dataObj->$propName);
                }

                $this->getDB()->update($tableName, $keyName, $dataObj->$keyName, $dataObj);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $cryptoService = Shop::Container()->getCryptoService();
        foreach ($this->properties as $tableName => $propNames) {
            $keyName = array_shift($propNames);
            $dataSet = $this->fetchAll(
                "SELECT $keyName, " . implode(', ', $propNames) .
                "   FROM $tableName"
            );

            foreach ($dataSet as $dataObj) {
                foreach ($propNames as $propName) {
                    if ($dataObj->$propName === null) {
                        continue;
                    }
                    $dataObj->$propName = $cryptoService->decryptXTEA($dataObj->$propName);
                    if (Text::is_utf8($dataObj->$propName)) {
                        $dataObj->$propName = Text::convertISO($dataObj->$propName);
                    }
                    $dataObj->$propName = $cryptoService->encryptXTEA($dataObj->$propName);
                }

                $this->getDB()->update($tableName, $keyName, $dataObj->$keyName, $dataObj);
            }
        }
    }
}
