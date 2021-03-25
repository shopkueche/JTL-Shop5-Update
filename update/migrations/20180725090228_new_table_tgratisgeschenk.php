<?php
/**
 * new table tgratisgeschenk
 *
 * @author mh
 * @created Wed, 25 Jul 2018 09:02:28 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180725090228
 */
class Migration_20180725090228 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'New table tgratisgeschenk';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tgratisgeschenk` (
                `kGratisGeschenk` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `kArtikel`        INT(10) UNSIGNED NOT NULL,
                `kWarenkorb`      INT(10) UNSIGNED NOT NULL,
                `nAnzahl`         INT(10) UNSIGNED NOT NULL,
                 PRIMARY KEY (`kGratisGeschenk`),        
                 INDEX `kWarenkorb` (`kWarenkorb`)
            ) ENGINE = InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE `tgratisgeschenk`');
    }
}
