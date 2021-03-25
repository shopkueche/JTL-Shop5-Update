<?php
/**
 * change the column type of tuploadschemasprache.cBeschreibung to TEXT to hold longer descriptions
 *
 * @author dr
 * @created Fr, 07 Oct 2016 16:19:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161007161900
 */
class Migration_20161007161900 extends Migration implements IMigration
{
    protected $author = 'dr';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tuploadschemasprache MODIFY cBeschreibung TEXT NOT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tuploadschemasprache MODIFY cBeschreibung VARCHAR(45) NOT NULL');
    }
}
