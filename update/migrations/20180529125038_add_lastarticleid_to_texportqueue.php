<?php
/**
 * Add LastArticleID to texportqueue
 *
 * @author fp
 * @created Tue, 29 May 2018 12:50:38 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180529125038
 */
class Migration_20180529125038 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add LastArticleID to texportqueue';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE `texportqueue` ADD COLUMN `nLastArticleID` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `nLimit_m`'
        );
        $this->execute(
            'ALTER TABLE `tjobqueue` ADD COLUMN `nLastArticleID` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `nLimitm`'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE `texportqueue` DROP COLUMN `nLastArticleID`'
        );
        $this->execute(
            'ALTER TABLE `tjobqueue` DROP COLUMN `nLastArticleID`'
        );
    }
}
