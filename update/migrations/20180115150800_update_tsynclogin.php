<?php
/**
 * update tsynclogin table
 *
 * @author fm
 * @created Mon, 15 Jan 2018 15:08:00 +0100
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180115150800
 */
class Migration_20180115150800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Update tsynclogin table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $values = $this->getDB()->select('tsynclogin', [], []);

        $this->execute('DELETE FROM `tsynclogin`');
        $this->execute(
            'ALTER TABLE `tsynclogin`
                ADD COLUMN `kSynclogin` INT NOT NULL DEFAULT 1 FIRST,
                ADD PRIMARY KEY (`kSynclogin`)'
        );
        $this->execute(
            "ALTER TABLE `tsynclogin`
                CHANGE COLUMN `cMail` `cMail` VARCHAR(255)     NULL DEFAULT '',
                CHANGE COLUMN `cName` `cName` VARCHAR(255) NOT NULL,
                CHANGE COLUMN `cPass` `cPass` VARCHAR(255) NOT NULL"
        );

        $values->kSynclogin = 1;
        $passInfo           = password_get_info($values->cPass);
        if ($passInfo['algo'] === 0) {
            $values->cPass = password_hash($values->cPass, PASSWORD_DEFAULT);
        }

        $this->getDB()->insert('tsynclogin', $values);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $columns = $this->getDB()->query("SHOW COLUMNS FROM tsynclogin LIKE 'kSynclogin'", ReturnType::SINGLE_OBJECT);

        if ($columns && $columns->Field === 'kSynclogin') {
            $this->execute(
                'ALTER TABLE `tsynclogin`
                    DROP COLUMN `kSynclogin`,
                    DROP PRIMARY KEY'
            );
        }
    }
}
