<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200909150300
 */
class Migration_20200909150300 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add license widget';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("INSERT INTO `tadminwidgets` (`kPlugin`, `cTitle`, `cClass`, 
             `eContainer`, `cDescription`, `nPos`, `bExpanded`, `bActive`) 
             VALUES ('0', 'Lizenzen', 'LicensedItemUpdates', 'center', 'Zeigt Lizenznformationen an', '0', '1', '1')"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tadminwidgets` WHERE kPlugin = 0 AND cClass = 'LicensedItemUpdates'");
    }
}
