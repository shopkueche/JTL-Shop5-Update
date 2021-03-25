<?php
/**
 * Add index on tnewsletterempfaenger.kKunde
 *
 * @author fp
 * @created Thu, 22 Dec 2016 13:50:18 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20161222135018
 */
class Migration_20161222135018 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add index on tnewsletterempfaenger.kKunde';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tnewsletterempfaenger ADD INDEX kKunde (kKunde)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tnewsletterempfaenger DROP INDEX kKunde');
    }
}
