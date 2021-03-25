<?php
/**
 * Remove tkategorieartikelgesamt
 *
 * @author fp
 * @created Tue, 20 Jun 2017 10:35:19 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170620103519
 */
class Migration_20170620103519 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Remove tkategorieartikelgesamt';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DROP TABLE tkategorieartikelgesamt');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('CREATE TABLE tkategorieartikelgesamt (
            kArtikel       int(10) unsigned NOT NULL,
            kOberKategorie int(10) unsigned NOT NULL,
            kKategorie     int(10) unsigned NOT NULL,
            nLevel         int(10) unsigned NOT NULL,
            KEY kArtikel (kArtikel,kOberKategorie)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1');
        $this->execute('INSERT INTO tkategorieartikelgesamt (kArtikel, kOberKategorie, kKategorie, nLevel) (
            SELECT DISTINCT tkategorieartikel.kArtikel, oberkategorie.kOberKategorie, oberkategorie.kKategorie, oberkategorie.nLevel - 1
                FROM tkategorieartikel
                INNER JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                INNER JOIN tkategorie oberkategorie ON tkategorie.lft BETWEEN oberkategorie.lft AND oberkategorie.rght
        )');
    }
}
