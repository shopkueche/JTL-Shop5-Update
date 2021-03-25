<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201110135300
 */
class Migration_20201110135300 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Remove availability sorting option';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'DELETE FROM `teinstellungenconfwerte` WHERE kEinstellungenConf = 190 AND cWert = 8'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "INSERT INTO `teinstellungenconfwerte` (kEinstellungenConf, cName, cWert, nSort) 
                VALUES (190, 'Verfügbarkeit', 8, 8)"
        );
    }
}
