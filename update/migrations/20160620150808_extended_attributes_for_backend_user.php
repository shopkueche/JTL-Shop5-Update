<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160620150808
 */
class Migration_20160620150808 extends Migration implements IMigration
{
    protected $author = 'fp';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tadminloginattribut` (
                `kAttribut`    INT          NOT NULL AUTO_INCREMENT,
                `kAdminlogin`  INT          NOT NULL,
                `cName`        VARCHAR(45)  NOT NULL,
                `cAttribValue` VARCHAR(512) NOT NULL DEFAULT '',
                `cAttribText`  TEXT             NULL,
                PRIMARY KEY (`kAttribut`),
                UNIQUE INDEX `cName_UNIQUE` (`kAdminlogin`, `cName`)) 
                ENGINE = MyISAM  DEFAULT CHARSET=latin1"
        );

        $this->execute(
            'CREATE TABLE IF NOT EXISTS `tcontentauthor` (
                `kContentAuthor`  INT          NOT NULL AUTO_INCREMENT,
                `cRealm`          VARCHAR(45)  NOT NULL,
                `kAdminlogin`     INT          NOT NULL,
                `kContentId`      INT          NOT NULL,
                PRIMARY KEY (`kContentAuthor`),
                UNIQUE INDEX `cRealm_UNIQUE` (`cRealm`, `kContentId`)) 
                ENGINE = MyISAM  DEFAULT CHARSET=latin1'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tcontentauthor`');
        $this->execute('DROP TABLE IF EXISTS `tadminloginattribut`');
    }
}
