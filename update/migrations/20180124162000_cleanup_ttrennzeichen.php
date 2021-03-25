<?php
/**
 * Rebuild ttrennzeichen and add unique index
 *
 * @author fm
 * @created Wed, 18 Jan 2018 16:20:00 +0100
 */

use JTL\Catalog\Separator;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180124162000
 */
class Migration_20180124162000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Rebuild ttrennzeichen and add unique index';

    /**
     * @inheritDoc
     */
    public function up()
    {
        Separator::migrateUpdate();
        $this->execute('ALTER TABLE `ttrennzeichen` ADD UNIQUE INDEX `unique_lang_unit` (`kSprache`, `nEinheit`)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `ttrennzeichen` DROP INDEX `unique_lang_unit`');
    }
}
