<?php declare(strict_types=1);
/**
 * Create notifications ignore table.
 *
 * @author fp
 * @created Wed, 23 Sep 2020 14:28:33 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200923142833
 */
class Migration_20200923142833 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Create notifications ignore table.';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('CREATE TABLE tnotificationsignore (
            id                int         NOT NULL AUTO_INCREMENT,
            user_id           int         NOT NULL,
            notification_hash varchar(40) NOT NULL,
            created           datetime,
            PRIMARY KEY (id),
            UNIQUE KEY idx_notificationignore_hash_uq (user_id, notification_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS tnotificationsignore');
    }
}
