<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201005121800
 */
class Migration_20201005121800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Disable old JTL Widgets plugin';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE tplugin SET nStatus = 1 WHERE cName = 'JTL Widgets' AND nVersion = 100 AND nStatus = 2");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
