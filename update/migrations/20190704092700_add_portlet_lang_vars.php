<?php
/**
 * Add portlet lang vars
 *
 * @author mh
 * @created Thu, 4 July 2019 09:27:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190704092700
 */
class Migration_20190704092700 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add portlet lang vars';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'days', 'Tage');
        $this->setLocalization('eng', 'global', 'days', 'Days');
        $this->setLocalization('ger', 'global', 'hours', 'Stunden');
        $this->setLocalization('eng', 'global', 'hours', 'Hours');
        $this->setLocalization('ger', 'global', 'minutes', 'Minuten');
        $this->setLocalization('eng', 'global', 'minutes', 'Minutes');
        $this->setLocalization('ger', 'global', 'seconds', 'Sekunden');
        $this->setLocalization('eng', 'global', 'seconds', 'Seconds');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('days');
        $this->removeLocalization('hours');
        $this->removeLocalization('minutes');
        $this->removeLocalization('seconds');
    }
}
