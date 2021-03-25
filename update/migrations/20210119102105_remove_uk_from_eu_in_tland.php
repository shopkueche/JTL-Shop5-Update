<?php declare(strict_types=1);
/**
 * remove UK from EU in tland
 *
 * @author cr
 * @created Tue, 19 Jan 2021 10:21:05 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210119102105
 */
class Migration_20210119102105 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'remove UK from EU in tland';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE tland SET nEU=0 WHERE cISO='GB'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE tland SET nEU=1 WHERE cISO='GB'");
    }
}
