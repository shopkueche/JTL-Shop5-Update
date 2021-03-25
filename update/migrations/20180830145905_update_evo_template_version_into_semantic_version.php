<?php
/**
 * Update evo template version into semantic version
 *
 * @author msc
 * @created Thu, 30 Aug 2018 14:59:05 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180830145905
 */
class Migration_20180830145905 extends Migration implements IMigration
{
    protected $author      = 'msc';
    protected $description = 'Update evo template version into semantic version';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE `ttemplate` SET `version` = '5.0.0' WHERE `cTemplate` = 'Evo' AND `eTyp` = 'standard'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE `ttemplate` SET `version` = '5.0' WHERE `cTemplate` = 'Evo' AND `eTyp` = 'standard'");
    }
}
