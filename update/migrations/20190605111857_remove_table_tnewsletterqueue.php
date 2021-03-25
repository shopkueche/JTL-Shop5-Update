<?php
/**
 * Remove table tnewsletterqueue
 *
 * @author cr
 * @created Wed, 05 Jun 2019 11:18:57 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190605111857
 */
class Migration_20190605111857 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Remove table tnewsletterqueue';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DROP TABLE tnewsletterqueue');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'CREATE TABLE tnewsletterqueue (
                kNewsletterQueue int(10) unsigned NOT NULL AUTO_INCREMENT,
                kNewsletter int(10) unsigned NOT NULL,
                nAnzahlEmpfaenger int(10) unsigned NOT NULL,
                dStart datetime NOT NULL,
            PRIMARY KEY (kNewsletterQueue),
            KEY kNewsletter (kNewsletter)
            ) 
            ENGINE = MyISAM 
            DEFAULT CHARSET = latin1'
        );
    }
}
