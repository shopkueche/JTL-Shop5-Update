<?php declare(strict_types=1);

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201218142100
 */
class Migration_20201218142100 extends Migration implements IMigration
{
    protected $author      = 'fm';

    protected $description = 'Add subject for MAILTEMPLATE_CHECKBOX_SHOPBETREIBER';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $tplID = $this->getDB()->queryPrepared(
            'SELECT kEmailvorlage AS id
                FROM temailvorlage
                WHERE cModulId = :nm',
            ['nm' => MAILTEMPLATE_CHECKBOX_SHOPBETREIBER],
            ReturnType::SINGLE_OBJECT
        );
        if ($tplID === false) {
            return;
        }
        $tplID = (int)$tplID->id;
        $this->execute(
            'UPDATE `temailvorlagespracheoriginal` 
                SET `cBetreff` = \'Auswahl einer Checkboxoption\' 
                WHERE `kEmailvorlage` = ' . $tplID . ' AND `kSprache` = 1 AND cBetreff = \'\''
        );
        $this->execute(
            'UPDATE `temailvorlagespracheoriginal`
                SET `cBetreff` = \'Checkbox option checked\' 
                WHERE `kEmailvorlage` = ' . $tplID . ' AND `kSprache` = 2 AND cBetreff = \'\''
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
