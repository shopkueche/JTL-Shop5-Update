<?php
/**
 * add news category image row
 *
 * @author dr
 * @created Thu, 28 Apr 2016 16:27:06 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160510150906
 */
class Migration_20160510150906 extends Migration implements IMigration
{
    protected $author = 'dr';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tnewskategorie ADD `cPreviewImage` VARCHAR(255)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->dropColumn('tnewskategorie', 'cPreviewImage');
    }
}
