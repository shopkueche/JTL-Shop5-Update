<?php
/**
 * change the column type of tkupon.cArtikel to MEDIUMTEXT to store more product numbers than just about 5000
 *
 * @author dr
 * @created Mon, 01 Nov 2016 08:26:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161101082600
 */
class Migration_20161101082600 extends Migration implements IMigration
{
    protected $author = 'dr';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tkupon MODIFY cArtikel MEDIUMTEXT NOT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tkupon MODIFY cArtikel TEXT NOT NULL');
    }
}
