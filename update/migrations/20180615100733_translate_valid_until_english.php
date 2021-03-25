<?php
/**
 * translate validUntil english
 *
 * @author mh
 * @created Fri, 15 Jun 2018 10:07:33 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180615100733
 */
class Migration_20180615100733 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Translate validUntil global english';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('eng', 'global', 'validUntil', 'valid until');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
