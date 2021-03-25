<?php
/**
 * Create status table for or-filtered attributes
 *
 * @author fp
 * @created Fri, 11 Sep 2020 13:55:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200911135500
 */
class Migration_20200911135500 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Create new index for similiar products';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tartikel ADD INDEX kVaterArtikel_UQ2 (nIstVater, kVaterArtikel, kArtikel)');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tartikel DROP INDEX kVaterArtikel_UQ2');
    }
}
