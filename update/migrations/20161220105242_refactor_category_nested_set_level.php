<?php
/**
 * Refactor category nested set level
 *
 * @author fp
 * @created Tue, 20 Dec 2016 10:52:42 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161220105242
 */
class Migration_20161220105242 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Refactor category nested set level';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE tkategorie
                ADD COLUMN nLevel int(10) unsigned NOT NULL DEFAULT 0 AFTER rght'
        );

        $this->execute(
            'UPDATE tkategorie
                SET nLevel = (
                    SELECT nLevel 
                    FROM tkategorielevel 
                    WHERE tkategorielevel.kKategorie = tkategorie.kKategorie)'
        );

        $this->execute(
            'DROP TABLE tkategorielevel'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'CREATE TABLE tkategorielevel (
                kKategorieLevel     int(10) unsigned NOT NULL AUTO_INCREMENT,
                kKategorie          int(10) unsigned NOT NULL,
                nLevel              int(10) unsigned NOT NULL,
                PRIMARY KEY (kKategorieLevel),
                UNIQUE KEY kKategorie (kKategorie))'
        );

        $this->execute(
            'INSERT INTO tkategorielevel (kKategorie, nLevel)
                SELECT kKategorie, nLevel FROM tkategorie'
        );

        $this->execute(
            'ALTER TABLE tkategorie
                DROP COLUMN nLevel'
        );
    }
}
