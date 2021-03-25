<?php
/**
 * Create column nMehrfachauswahl for tmerkmal
 *
 * @author fm
 * @created Thu, 11 Mai 2017 15:34:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170511153400
 */
class Migration_20170511153400 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = /** @lang text */
        'Create column nMehrfachauswahl in tmerkmal';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE tmerkmal ADD COLUMN nMehrfachauswahl TINYINT NOT NULL DEFAULT 0'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE tmerkmal DROP COLUMN nMehrfachauswahl'
        );
    }
}
