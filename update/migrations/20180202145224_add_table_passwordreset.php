<?php
/**
 * add_table_passwordreset
 *
 * @author mschop
 * @created Fri, 02 Feb 2018 14:52:24 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180202145224
 */
class Migration_20180202145224 extends Migration implements IMigration
{
    protected $author      = 'mschop';
    protected $description = 'Add Table tpasswordreset';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'CREATE TABLE tpasswordreset(
            kKunde INT PRIMARY KEY ,
            cKey VARCHAR(255) UNIQUE,
            dExpires DATETIME
          ) ENGINE=InnoDB COLLATE utf8_unicode_ci;
          CREATE INDEX tpasswordreset_cKey ON tpasswordreset(cKey);
          ALTER TABLE tkunde DROP COLUMN cResetPasswordHash;
        '
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE tpasswordreset');
        $this->execute('ALTER TABLE tkunde ADD COLUMN cResetPasswordHash VARCHAR(255)');
    }
}
