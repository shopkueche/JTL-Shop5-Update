<?php
/**
 * create columns for dynamic options sources
 *
 * @author fm
 * @created Fri, 20 May 2016 14:21:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160520142100
 */
class Migration_20160520142100 extends Migration implements IMigration
{
    protected $author = 'fm';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tplugineinstellungenconf` ADD COLUMN `cSourceFile` VARCHAR(255) NULL DEFAULT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tplugineinstellungenconf` DROP COLUMN `cSourceFile`');
    }
}
