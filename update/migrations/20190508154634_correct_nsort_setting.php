<?php
/**
 * correct_nsort_setting
 *
 * @author mh
 * @created Wed, 08 May 2019 15:46:34 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190508154634
 */
class Migration_20190508154634 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Correct nsort of artikel_lagerampel_keinlager';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("UPDATE teinstellungenconf SET nSort=505 WHERE cWertName = 'artikel_lagerampel_keinlager'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("UPDATE teinstellungenconf SET nSort=500 WHERE cWertName = 'artikel_lagerampel_keinlager'");
    }
}
