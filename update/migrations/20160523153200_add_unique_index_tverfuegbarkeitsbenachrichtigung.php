<?php
/**
 * add unique index to tverfuegbarkeitsbenachrichtigung
 *
 * @author ms
 * @created Mon, 23 May 2016 15:32:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Update\MigrationHelper;

/**
 * Class Migration_20160523153200
 */
class Migration_20160523153200 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DELETE data1 FROM `tverfuegbarkeitsbenachrichtigung` data1, `tverfuegbarkeitsbenachrichtigung` data2 
                           WHERE  data1.`cMail` = data2.`cMail` 
                             AND data1.`kArtikel` = data2.`kArtikel` 
                             AND data1.`kVerfuegbarkeitsbenachrichtigung` < data2.`kVerfuegbarkeitsbenachrichtigung`');
        MigrationHelper::createIndex('tverfuegbarkeitsbenachrichtigung', ['cMail', 'kArtikel'], 'idx_cMail_kArtikel', true);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        MigrationHelper::dropIndex('tverfuegbarkeitsbenachrichtigung', 'idx_cMail_kArtikel');
    }
}
