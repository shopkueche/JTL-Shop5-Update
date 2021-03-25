<?php
/**
 * Move extension viewer widget to status.php
 *
 * @author mh
 * @created Tue, 01 Sep 2020 12:32:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200901123200
 */
class Migration_20200901123200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Move extension viewer widget to status.php';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("DELETE FROM `tadminwidgets` WHERE cClass='ExtensionViewer'");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "INSERT INTO tadminwidgets (
                    kPlugin, cTitle, cClass, eContainer, cDescription, nPos, bExpanded, bActive
                )
                VALUES (0, 'Erweiterungen', 'ExtensionViewer', 'center', 'Zeigt alle aktiven Erweiterungen', 4, 1, 1)"
        );
    }
}
