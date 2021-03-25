<?php
/**
 * Add missing linkgroup box names
 *
 * @author mh
 * @created Thu, 9 Jan 2020 11:45:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200109114500
 */
class Migration_20200109114500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add missing linkgroup box names';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "INSERT INTO tboxsprache (kBox, cISO, cTitel, cInhalt)
                SELECT tboxen.kBox, tlinkgruppesprache.cISOSprache, tlinkgruppesprache.cName, ''
                    FROM tboxen
                    INNER JOIN tlinkgruppesprache
                        ON tlinkgruppesprache.kLinkgruppe = tboxen.kCustomID
                    LEFT JOIN tboxsprache
                        ON tboxsprache.kBox = tboxen.kBox
                        AND tboxsprache.cISO = tlinkgruppesprache.cISOSprache
                    INNER JOIN tsprache ON tsprache.cISO = tlinkgruppesprache.cISOSprache
                    WHERE kCustomID > 0
                        AND tboxsprache.kBoxSprache IS NULL;"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
