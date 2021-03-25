<?php
/**
 * sets nofollow for special pages
 *
 * @author ms
 * @created Tue, 28 Feb 2017 16:31:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170228163100
 */
class Migration_20170228163100 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'sets nofollow for special pages';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE `tlink` SET `cNoFollow` = 'Y' WHERE `nLinkart`= '11' OR `nLinkart`= '12' OR `nLinkart`= '24';"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "UPDATE `tlink` SET `cNoFollow` = 'N' WHERE `nLinkart`= '11' OR `nLinkart`= '12' OR `nLinkart`= '24';"
        );
    }
}
