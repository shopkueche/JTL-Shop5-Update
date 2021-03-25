<?php
/**
 * Change kSprache column to store an IETF language tag
 *
 * @author dr
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190319103100
 */
class Migration_20190319103100 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Change kSprache column to store an IETF language tag';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tadminlogin DROP COLUMN kSprache');
        $this->execute("ALTER TABLE tadminlogin ADD COLUMN language VARCHAR(35) DEFAULT 'de-DE'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $stdLang = (int)$this->getDB()->select('tsprache', 'cShopStandard', 'Y')->kSprache;
        $this->execute("ALTER TABLE tadminlogin ADD COLUMN kSprache TINYINT(3) UNSIGNED DEFAULT $stdLang");
        $this->execute('ALTER TABLE tadminlogin DROP COLUMN language');
    }
}
