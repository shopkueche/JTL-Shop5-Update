<?php
/**
 * add column cMail to tkuponkunde
 *
 * @author sh
 * @created Mon, 22 Feb 2016 13:51:31 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160222135131
 */
class Migration_20160222135131 extends Migration implements IMigration
{
    protected $author = 'sh';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tkuponkunde ADD `cMail` VARCHAR(255) AFTER `kKunde`');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tkuponkunde', 'cMail');
    }
}
