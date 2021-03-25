<?php
/**
 * change the column type of tlinkgruppensprache.kLinkgruppe to INT
 *
 * @author ms
 * @created Tue, 09 Nov 2016 11:18:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161109111800
 */
class Migration_20161109111800 extends Migration implements IMigration
{
    protected $author = 'ms';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE tlinkgruppesprache CHANGE COLUMN kLinkgruppe kLinkgruppe INT UNSIGNED NOT NULL DEFAULT '0';");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("ALTER TABLE tlinkgruppesprache CHANGE COLUMN kLinkgruppe kLinkgruppe TINYINT(3) UNSIGNED NOT NULL DEFAULT '0';");
    }
}
