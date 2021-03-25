<?php
/**
 * remove rma special page
 *
 * @author fm
 * @created Wed, 09 May 2018 15:21:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180509152100
 */
class Migration_20180509152100 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'removes rma special page';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("DELETE FROM tspezialseite WHERE cDateiname = 'rma.php'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("INSERT INTO tspezialseite VALUES (23,0,'Warenrücksendung','rma.php',28,28)");
    }
}
