<?php
/**
 * new category structure
 *
 * @author fm
 * @created Mo, 11 Apr 2016 17:16:10 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160411171610
 */
class Migration_20160411171610 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE `tartikelabnahme` CHANGE COLUMN `fMindestabnahme` `fMindestabnahme` DOUBLE NULL DEFAULT '0';
                        UPDATE `tartikelabnahme` SET `fMindestabnahme` = CAST(fMindestabnahme AS DECIMAL(10,4)) WHERE kArtikel > 0 AND fMindestabnahme > 0;");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("ALTER TABLE `tartikelabnahme` CHANGE COLUMN `fMindestabnahme` `fMindestabnahme` FLOAT NULL DEFAULT '0'");
    }
}
