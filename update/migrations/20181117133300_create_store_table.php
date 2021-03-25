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
 * Class Migration_20181117133300
 */
class Migration_20181117133300 extends Migration implements IMigration
{
    protected $author      = 'aj';
    protected $description = 'create store table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'CREATE TABLE `tstoreauth` (
                `auth_code` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
                `access_token` mediumtext COLLATE utf8_unicode_ci,
                `created_at` datetime NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE `tstoreauth`');
    }
}
