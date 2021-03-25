<?php
/**
 * Rename row in tkampagne and tkampagnedef to not blow up the table-width in statistics overview in backend
 *
 * @author dr
 * @created Mo, 12 Sep 2016 14:56:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160912145600
 */
class Migration_20160912145600 extends Migration implements IMigration
{
    protected $author = 'dr';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "UPDATE tkampagne
                SET cName = 'Verfügbarkeits-Benachrichtigungen' WHERE kKampagne = 1"
        );
        $this->execute(
            "UPDATE tkampagnedef
                SET cName = 'Verfügbarkeits-Anfrage' WHERE kKampagneDef = 6"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "UPDATE tkampagne
                SET cName = 'Verfügbarkeitsbenachrichtigungen' WHERE kKampagne = 1"
        );
        $this->execute(
            "UPDATE tkampagnedef
                SET cName = 'Verfügbarkeitsanfrage' WHERE kKampagneDef = 6"
        );
    }
}
