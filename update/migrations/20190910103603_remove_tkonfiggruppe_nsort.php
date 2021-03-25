<?php
/**
 * Remove tkonfiggruppe.nSort
 *
 * @author fp
 * @created Tue, 10 Sep 2019 10:36:03 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Migration
 */
class Migration_20190910103603 extends Migration implements IMigration
{
    protected $author      = 'fpr';
    protected $description = 'Remove tkonfiggruppe.nSort';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tkonfiggruppe DROP COLUMN nSort');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tkonfiggruppe ADD COLUMN nSort INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER nTyp');
    }
}
