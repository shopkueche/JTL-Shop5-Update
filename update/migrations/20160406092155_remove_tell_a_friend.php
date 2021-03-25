<?php
/**
 * tell_a_friend
 *
 * @author wp
 * @created Wed, 06 Apr 2016 09:21:55 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160406092155
 */
class Migration_20160406092155 extends Migration implements IMigration
{
    protected $author = 'wp';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tartikelweiterempfehlenhistory`;');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('CREATE TABLE `tartikelweiterempfehlenhistory` (
                      `kArtikelWeiterempfehlenHistory` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `kArtikel` int(10) unsigned NOT NULL,
                      `kSprache` int(10) unsigned NOT NULL,
                      `kKunde` int(10) unsigned NOT NULL,
                      `cName` varchar(255) NOT NULL,
                      `cEmail` varchar(255) NOT NULL,
                      `cIP` varchar(255) NOT NULL,
                      `dErstellt` datetime NOT NULL,
                      PRIMARY KEY (`kArtikelWeiterempfehlenHistory`),
                      KEY `kArtikel` (`kArtikel`,`kSprache`,`cIP`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;');
    }
}
