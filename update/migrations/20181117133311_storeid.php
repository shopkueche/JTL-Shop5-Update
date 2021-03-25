<?php
/**
 * create store table
 *
 * @author aj
 * @created Mon, 17 Nov 2018 13:33:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181117133311
 */
class Migration_20181117133311 extends Migration implements IMigration
{
    protected $author      = 'aj';
    protected $description = 'add plugin store id';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tplugin ADD COLUMN cStoreID varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL AFTER cPluginID');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tplugin', 'cStoreID');
    }
}
