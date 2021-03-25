<?php
/**
 * Add aria labels
 *
 * @author ms
 * @created Wed, 14 Aug 2019 08:48:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190814084800
 */
class Migration_20190814084800 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'Add aria language vars';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'aria', 'visit_us_on', 'Besuchen Sie uns auch auf %s');
        $this->setLocalization('eng', 'aria', 'visit_us_on', 'visit us on %s');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('visit_us_on');
    }
}
