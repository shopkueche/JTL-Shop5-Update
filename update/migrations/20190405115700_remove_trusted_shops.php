<?php
/**
 * @author fm
 * @created Fri, 05 Apr 2019 11:57:00 +0100
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190405115700
 */
class Migration_20190405115700 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'remove trusted shops';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $boxIDs = $this->getDB()->query(
            'SELECT kBox FROM tboxen WHERE kBoxvorlage IN (17, 18)',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($boxIDs as $boxID) {
            $this->getDB()->delete('tboxensichtbar', 'kBox', $boxID->kBox);
            $this->getDB()->delete('tboxsprache', 'kBox', $boxID->kBox);
        }
        $this->execute('DELETE FROM tboxvorlage WHERE kBoxvorlage IN (17, 18)');
        $this->execute('DELETE FROM tboxen WHERE kBoxvorlage IN (17, 18)');
        $configs = $this->getDB()->query(
            'SELECT kEinstellungenConf AS id FROM teinstellungenconf WHERE kEinstellungenSektion = 117',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($configs as $config) {
            $this->getDB()->delete('teinstellungenconfwerte', 'kEinstellungenConf', $config->id);
        }
        $this->execute('DELETE FROM teinstellungen WHERE kEinstellungenSektion = 117');
        $this->execute('DELETE FROM teinstellungenconf WHERE kEinstellungenSektion = 117');
        $this->execute("DELETE FROM tadminrecht WHERE cRecht = 'ORDER_TRUSTEDSHOPS_VIEW'");
        $this->execute("DELETE FROM tadminrechtegruppe WHERE cRecht = 'ORDER_TRUSTEDSHOPS_VIEW'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
