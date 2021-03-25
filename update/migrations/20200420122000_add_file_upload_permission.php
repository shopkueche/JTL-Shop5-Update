<?php
/**
 * Add file upload permission
 *
 * @author mh
 * @created Mon, 20 Apr 2020 12:22:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200420122000
 */
class Migration_20200420122000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add file upload permission';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("INSERT INTO `tadminrecht` VALUES ('IMAGE_UPLOAD', 'File upload');");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='IMAGE_UPLOAD';");
    }
}
