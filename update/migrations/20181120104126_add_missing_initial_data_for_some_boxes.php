<?php
/**
 * add_missing_initial_data_for_some_boxes
 *
 * @author mh
 * @created Tue, 20 Nov 2018 10:41:26 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181120104126
 */
class Migration_20181120104126 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Add missing initial data for some boxes';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $missingBoxes = [36, 38, 40, 21, 42];

        foreach ($missingBoxes as $missingBox) {
            $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES ($missingBox, 'left', 1)");
            $this->execute("INSERT IGNORE INTO `tboxenanzeige` (`nSeite`, `ePosition`, `bAnzeigen`) VALUES ($missingBox, 'bottom', 1)");
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
