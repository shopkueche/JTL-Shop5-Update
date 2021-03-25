<?php
/**
 * Persistent deliverytime in orders
 *
 * @author root
 * @created Wed, 10 Aug 2016 10:20:32 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160810102032
 */
class Migration_20160810102032 extends Migration implements IMigration
{
    protected $author = 'fp';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE tbestellung 
                ADD COLUMN nLongestMinDelivery INT NOT NULL DEFAULT 0 AFTER cVersandInfo,
                ADD COLUMN nLongestMaxDelivery INT NOT NULL DEFAULT 0 AFTER nLongestMinDelivery'
        );
        $this->execute(
            'ALTER TABLE twarenkorbpos 
                ADD COLUMN nLongestMinDelivery INT NOT NULL DEFAULT 0,
                ADD COLUMN nLongestMaxDelivery INT NOT NULL DEFAULT 0 AFTER nLongestMinDelivery'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE tbestellung 
                DROP COLUMN nLongestMinDelivery,
                DROP COLUMN nLongestMaxDelivery'
        );
        $this->execute(
            'ALTER TABLE twarenkorbpos 
                DROP COLUMN nLongestMinDelivery,
                DROP COLUMN nLongestMaxDelivery'
        );
    }
}
