<?php
/**
 * Remove tpreise
 *
 * @author fp
 * @created Mon, 15 Apr 2019 16:43:25 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190415164325
 */
class Migration_20190415164325 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Remove tpreise';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS tpreise');
        $this->execute('UPDATE tpreis SET kKunde = 0 WHERE kKunde IS NULL');

        $this->execute(
            'DELETE FROM tpreis
                 WHERE kPreis IN (SELECT * FROM (
                    SELECT DISTINCT tp1.kPreis
                    FROM tpreis tp1
                    LEFT JOIN tpreis tp2 ON tp2.kArtikel = tp1.kArtikel
                        AND tp2.kKundengruppe = tp1.kKundengruppe
                        AND tp2.kKunde = tp1.kKunde
                        AND tp2.kPreis < tp1.kPreis
                    WHERE tp2.kPreis IS NOT NULL) AS i)'
        );
        if ($this->fetchOne("SHOW INDEX FROM tpreis WHERE KEY_NAME = 'kArtikel'")) {
            $this->execute('DROP INDEX kArtikel ON tpreis');
        }
        $this->execute('CREATE UNIQUE INDEX kArtikel on tpreis(kArtikel, kKundengruppe, kKunde)');

        $this->execute(
            'DELETE FROM tpreisverlauf
                 WHERE kPreisverlauf IN (SELECT * FROM (
                    SELECT DISTINCT tp1.kPreisverlauf
                    FROM tpreisverlauf tp1
                    LEFT JOIN tpreisverlauf tp2 ON tp2.kArtikel = tp1.kArtikel
                        AND tp2.kKundengruppe = tp1.kKundengruppe
                        AND tp2.dDate = tp1.dDate
                        AND tp2.kPreisverlauf < tp1.kPreisverlauf
                    WHERE tp2.kPreisverlauf IS NOT NULL) AS i)'
        );
        if ($this->fetchOne("SHOW INDEX FROM tpreisverlauf WHERE KEY_NAME = 'kArtikel'")) {
            $this->execute('DROP INDEX kArtikel ON tpreisverlauf');
        }
        $this->execute('CREATE UNIQUE INDEX kArtikel on tpreisverlauf(kArtikel, kKundengruppe, dDate)');

        $this->execute(
            'DELETE FROM tpreisdetail
                 WHERE kPreisDetail IN (SELECT * FROM (
                    SELECT DISTINCT tp1.kPreisDetail
                    FROM tpreisdetail tp1
                    LEFT JOIN tpreisdetail tp2 ON tp2.kPreis = tp1.kPreis
                        AND tp2.nAnzahlAb = tp1.nAnzahlAb
                        AND tp2.kPreisDetail < tp1.kPreisDetail
                    WHERE tp2.kPreisDetail IS NOT NULL) AS i)'
        );
        if ($this->fetchOne("SHOW INDEX FROM tpreisdetail WHERE KEY_NAME = 'kPreis_nAnzahlAb'")) {
            $this->execute('DROP INDEX kPreis_nAnzahlAb ON tpreisdetail');
        }
        $this->execute('CREATE UNIQUE INDEX kPreis_nAnzahlAb ON tpreisdetail(kPreis, nAnzahlAb)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        if ($this->fetchOne("SHOW INDEX FROM tpreisdetail WHERE KEY_NAME = 'kPreis_nAnzahlAb'")) {
            $this->execute('DROP INDEX kPreis_nAnzahlAb ON tpreisdetail');
        }
        $this->execute('UPDATE tpreis SET kKunde = NULL WHERE kKunde = 0');
        if ($this->fetchOne("SHOW INDEX FROM tpreisverlauf WHERE KEY_NAME = 'kArtikel'")) {
            $this->execute('DROP INDEX kArtikel ON tpreisverlauf');
        }
        $this->execute('CREATE INDEX kArtikel on tpreisverlauf(kArtikel, kKundengruppe, dDate)');
        if ($this->fetchOne("SHOW INDEX FROM tpreis WHERE KEY_NAME = 'kArtikel'")) {
            $this->execute('DROP INDEX kArtikel ON tpreis');
        }
        $this->execute('CREATE INDEX kArtikel on tpreis(kArtikel, kKundengruppe, kKunde)');
        $this->execute(
            'CREATE TABLE tpreise (
                kKundengruppe   INT UNSIGNED DEFAULT 0  NOT NULL,
                kArtikel        INT UNSIGNED DEFAULT 0  NOT NULL,
                fVKNetto        DOUBLE                      NULL,
                nAnzahl1        INT UNSIGNED                NULL,
                nAnzahl2        INT UNSIGNED                NULL,
                nAnzahl3        INT UNSIGNED                NULL,
                nAnzahl4        INT UNSIGNED                NULL,
                nAnzahl5        INT UNSIGNED                NULL,
                fPreis1         DOUBLE                      NULL,
                fPreis2         DOUBLE                      NULL,
                fPreis3         DOUBLE                      NULL,
                fPreis4         DOUBLE                      NULL,
                fPreis5         DOUBLE                      NULL,
                PRIMARY KEY (kArtikel, kKundengruppe),
                KEY fVKNetto (fVKNetto)
            ) ENGINE = InnoDB COLLATE = utf8_unicode_ci'
        );
    }
}
