<?php
/**
 * Create unique index for tkundenattribut
 *
 * @author fp
 * @created Wed, 15 May 2019 16:05:10 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20190515160510 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Create unique index for tkundenattribut';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'DELETE FROM tkundenattribut
                 WHERE kKundenAttribut IN (SELECT * FROM (
                    SELECT DISTINCT tkundenattribut1.kKundenAttribut
                    FROM tkundenattribut tkundenattribut1
                    LEFT JOIN tkundenattribut tkundenattribut2 ON tkundenattribut2.kKunde = tkundenattribut1.kKunde
                        AND tkundenattribut2.kKundenfeld = tkundenattribut1.kKundenfeld
                        AND tkundenattribut2.kKundenAttribut < tkundenattribut1.kKundenAttribut
                    WHERE tkundenattribut2.kKundenAttribut IS NOT NULL) AS i)'
        );
        if ($this->fetchOne("SHOW INDEX FROM tkundenattribut WHERE KEY_NAME = 'kKundenfeld'")) {
            $this->execute('DROP INDEX kKundenfeld ON tkundenattribut');
        }
        $this->execute('CREATE UNIQUE INDEX kKundenfeld ON tkundenattribut(kKunde, kKundenfeld)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        if ($this->fetchOne("SHOW INDEX FROM tkundenattribut WHERE KEY_NAME = 'kKundenfeld'")) {
            $this->execute('DROP INDEX kKundenfeld ON tkundenattribut');
        }
        $this->execute('CREATE INDEX kKundenfeld ON tkundenattribut(kKundenfeld)');
    }
}
