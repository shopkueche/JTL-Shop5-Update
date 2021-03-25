<?php

/**
 * Remove did you know widget
 *
 * @author mh
 * @created Thu, 23 Jan 2020 12:23:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200123122200
 */
class Migration_20200123122200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove did you know widget';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("DELETE FROM `tadminwidgets` WHERE cClass='Duk'");
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
                VALUES (0, 'Wussten Sie schon', 'Duk', 'left', 'Nützliche Tipps zu JTL-Shop', 3, 1, 1)"
        );
    }
}
