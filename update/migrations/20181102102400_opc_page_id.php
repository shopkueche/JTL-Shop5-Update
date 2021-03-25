<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181102102400
 */
class Migration_20181102102400 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Change OPC page id type';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE topcpage DROP INDEX cPageId');
        $this->execute('ALTER TABLE topcpage MODIFY cPageId MEDIUMTEXT NOT NULL');
        $this->execute('ALTER TABLE topcpage ADD UNIQUE INDEX (cPageId(255), dPublishFrom)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE topcpage DROP INDEX cPageId');
        $this->execute('ALTER TABLE topcpage MODIFY cPageId CHAR(32) NOT NULL');
        $this->execute('ALTER TABLE topcpage ADD UNIQUE INDEX (cPageId, dPublishFrom)');
    }
}
