<?php
/**
 * Refactor data types
 * @author  fp
 * @created Mon, 20 Aug 2018 11:26:05 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180820112605
 */
class Migration_20180820112605 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Refactor data types for kKundengruppe';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $columns = $this->fetchAll(
            "SELECT TABLE_NAME, COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '" . DB_NAME . "'
                    AND COLUMN_NAME = 'kKundengruppe'
                    AND DATA_TYPE = 'tinyint'
                    AND TABLE_NAME NOT LIKE 'xplugin_%'"
        );
        foreach ($columns as $column) {
            $sql = /** @lang text */
                'ALTER TABLE `' . DB_NAME . '`.`' . $column->TABLE_NAME . '` CHANGE `kKundengruppe` `kKundengruppe` INT'
                . (strpos($column->COLUMN_TYPE, 'unsigned') !== false ? ' UNSIGNED' : '')
                . ($column->IS_NULLABLE === 'YES' ? ' NULL' : ' NOT NULL')
                . ($column->COLUMN_DEFAULT === null || $column->COLUMN_DEFAULT === 'NULL'
                    ? ($column->IS_NULLABLE === 'YES' ? ' DEFAULT NULL' : '')
                    : ' DEFAULT \'' . $column->COLUMN_DEFAULT . '\'');

            $this->execute($sql);
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // can not be undone...
    }
}
