<?php
/**
 * Add lang var for filter
 *
 * @author mh
 * @created Wed, 18 Dec 2019 10:30:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191218103000
 */
class Migration_20191218103000 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Add lang var for filter';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'filterCancel', 'Abbrechen');
        $this->setLocalization('eng', 'global', 'filterCancel', 'Cancel');
        $this->setLocalization('ger', 'global', 'filterShowItem', '%s Artikel ansehen');
        $this->setLocalization('eng', 'global', 'filterShowItem', 'Show %s items');

    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('filterCancel');
        $this->removeLocalization('filterShowItem');
    }
}
