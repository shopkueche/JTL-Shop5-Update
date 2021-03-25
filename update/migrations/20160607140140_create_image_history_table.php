<?php
/**
 * create image history table
 *
 * @author aj
 * @created Tue, 07 Jun 2016 14:01:40 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160607140140
 */
class Migration_20160607140140 extends Migration implements IMigration
{
    protected $author = 'aj';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'CREATE TABLE IF NOT EXISTS `tartikelpicthistory` (
                 `kArtikel` int(10) unsigned NOT NULL,
                 `cPfad` varchar(255) NOT NULL,
                 `nNr` tinyint(3) unsigned NOT NULL DEFAULT \'1\',
                  UNIQUE KEY `UNIQUE` (`kArtikel`,`nNr`,`cPfad`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1'
        );
        $this->execute(
            'REPLACE INTO `tartikelpicthistory` 
              (SELECT `kArtikel`, `cPfad`, `nNr` FROM `tartikelpict` WHERE `kBild` = 0)'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tartikelpicthistory`');
    }
}
