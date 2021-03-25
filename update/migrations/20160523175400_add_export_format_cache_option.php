<?php
/**
 * @author fm
 * @created Mon, 23 May 2016 17:54:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160523175400
 */
class Migration_20160523175400 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `texportformat` ADD COLUMN `nUseCache` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `texportformat` DROP COLUMN `nUseCache`');
    }
}
