<?php
/**
 * Delete unused fulltext keys
 *
 * @author fp
 * @created Wed, 13 Dec 2017 09:35:14 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20171213093514
 */
class Migration_20171213093514 extends Migration implements IMigration
{
    protected $author      = 'fpr';
    protected $description = 'Delete unused fulltext keys';

    /**
     * @inheritDoc
     */
    public function up()
    {
        foreach (['tartikel', 'tartikelsprache'] as $table) {
            $keys = $this->fetchAll(
                "SHOW INDEX FROM `{$table}` 
                    WHERE Index_type = 'FULLTEXT' 
                        AND Column_name IN ('cBeschreibung', 'cKurzBeschreibung')
                        AND Key_name != 'idx_{$table}_fulltext'"
            );
            if (is_array($keys)) {
                foreach ($keys as $key) {
                    $this->execute("ALTER TABLE $table DROP KEY {$key->Key_name}");
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        foreach (['tartikel', 'tartikelsprache'] as $table) {
            foreach (['cBeschreibung', 'cKurzBeschreibung'] as $fieldName) {
                $this->execute(
                    "ALTER TABLE `{$table}`
                        ADD FULLTEXT KEY `{$fieldName}` (`{$fieldName}`)"
                );
            }
        }
    }
}
