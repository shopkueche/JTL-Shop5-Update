<?php
/**
 * New table for price range
 *
 * @author fp
 * @created Wed, 04 Apr 2018 10:01:49 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180404100149
 */
class Migration_20180404100149 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'New table for price range';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tpricerange` (
                `kPriceRange`     INT(11)    UNSIGNED NOT NULL AUTO_INCREMENT,
                `kArtikel`        INT(11)    UNSIGNED NOT NULL,
                `kKundengruppe`   INT(11)    UNSIGNED NOT NULL DEFAULT 0,
                `kKunde`          INT(11)    UNSIGNED NOT NULL DEFAULT 0,
                `nRangeType`      TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
                `fVKNettoMin`     DOUBLE              NOT NULL DEFAULT 0,
                `fVKNettoMax`     DOUBLE              NOT NULL DEFAULT 0,
                `nLagerAnzahlMax` DOUBLE                  NULL,
                `dStart`          DATE                    NULL,
                `dEnde`           DATE                    NULL,
                PRIMARY KEY (`kPriceRange`),
                UNIQUE INDEX `tpricerange_uq` (`kArtikel` ASC, `kKundengruppe` ASC, `kKunde` ASC, `nRangeType` ASC)
            ) ENGINE = InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci'"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE `tpricerange`');
    }
}
