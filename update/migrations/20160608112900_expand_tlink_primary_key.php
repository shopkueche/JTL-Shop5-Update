<?php
/**
 * add new link types
 *
 * @author ms
 * @created Wed, 08 Jun 2016 11:29:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160608112900
 */
class Migration_20160608112900 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tlink` DROP PRIMARY KEY, ADD PRIMARY KEY (`kLink`, `kLinkgruppe`);');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tlink` DROP PRIMARY KEY, ADD PRIMARY KEY (`kLink`);');
    }
}
