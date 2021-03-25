<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200416093800
 */
class Migration_20200416093800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add license cron';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $ins               = new stdClass();
        $ins->frequency    = 4;
        $ins->jobType      = 'licensecheck';
        $ins->name         = 'licensecheck';
        $ins->startTime    = (new DateTime())->format('H:i:s');
        $ins->startDate    = (new DateTime())->format('Y-m-d H:i:s');
        $this->getDB()->insert('tcron', $ins);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM tcron WHERE jobType = 'licensecheck'");
    }
}
