<?php
/**
 * New Table for order attributes
 *
 * @author fp
 * @created Wed, 10 May 2017 09:41:18 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170510094118
 */
class Migration_20170510094118 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'New Table for order attributes';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'CREATE TABLE tbestellattribut (
                kBestellattribut INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                kBestellung      INT(10) UNSIGNED NOT NULL,
                cName            VARCHAR(255)     NOT NULL,
                cValue           TEXT                 NULL,
                PRIMARY KEY (kBestellattribut),
                UNIQUE KEY idx_kBestellung_cName_uq (kBestellung, cName)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE tbestellattribut');
    }
}
