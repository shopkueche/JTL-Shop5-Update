<?php
/**
 * moves the 404 page into the hidden linkgroup
 *
 * @author ms
 * @created Tue, 17 May 2016 13:23:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160517132300
 */
class Migration_20160517132300 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE `tlink` SET `kLinkgruppe` = 
              (SELECT `kLinkgruppe` FROM `tlinkgruppe` WHERE `cName` = 'hidden') WHERE `nLinkart`= '29';"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE `tlink` SET `kLinkgruppe`='0' WHERE `nLinkart`= '29';");
    }
}
