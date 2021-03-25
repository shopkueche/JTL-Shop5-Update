<?php
/**
 * Add language column to adminlogin table
 *
 * @author dr
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181130110200
 */
class Migration_20181130110200 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add language column to adminlogin table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $stdLang = (int)$this->getDB()->select('tsprache', 'cShopStandard', 'Y')->kSprache;
        $this->execute("ALTER TABLE tadminlogin ADD COLUMN kSprache TINYINT(3) UNSIGNED DEFAULT $stdLang");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tadminlogin DROP COLUMN kSprache');
    }
}
