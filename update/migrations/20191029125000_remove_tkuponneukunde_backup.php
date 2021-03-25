<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191029125000
 */
class Migration_20191029125000 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Remove tkuponneukunde_backup';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $tables = $this->fetchAll("SHOW TABLES LIKE 'tkuponkunde_backup'");

        if (count($tables) > 0) {
            $backupData = $this->fetchOne(
                'SELECT COUNT(*) cntBack
                    FROM (SELECT tkuponkunde_backup.kKupon,
                            SHA2(LOWER(tkuponkunde_backup.cMail), 256) AS cMail
                            FROM tkuponkunde_backup
                            INNER JOIN tkupon
                                    ON tkupon.kKupon = tkuponkunde_backup.kKupon
                            WHERE tkuponkunde_backup.cMail != \'\'
                            GROUP BY tkuponkunde_backup.cMail, tkuponkunde_backup.kKupon) back
                    LEFT JOIN tkuponkunde ON tkuponkunde.kKupon = back.kKupon
                             AND CONVERT(tkuponkunde.cMail USING utf8) = CONVERT(back.cMail USING utf8)'
            );

            if ((int)$backupData->cntBack === 0) {
                $this->execute('DROP TABLE tkuponkunde_backup');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
