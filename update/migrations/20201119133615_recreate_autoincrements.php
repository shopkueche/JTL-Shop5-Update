<?php declare(strict_types=1);

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201119133615
 */
class Migration_20201119133615 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Recreate missing autoincrement attributes';

    /**
     * @inheritDoc
     */
    public function up()
    {
        foreach ([
            'tkontaktbetreff' => [
                'column' => 'kKontaktBetreff',
                'refs' => [
                    'tkontaktbetreffsprache',
                    'tkontakthistory'
                ]
            ],
            'tsprachiso'      => [
                'column' => 'kSprachISO',
                'refs' => [
                    'tsprachlog',
                    'tsprachwerte'
                ]
            ],
            'ttext'           => [
                'column' => 'kText'
            ],
        ] as $table => $keyDef) {
            $keyColumn = $keyDef['column'];
            $lastValue = $this->db->query(
                'SELECT COALESCE(MAX('. $keyColumn . '), 0) + 1 AS value FROM ' . $table,
                ReturnType::SINGLE_OBJECT
            );
            $zeroKey   = $this->db->query(
                'SELECT '. $keyColumn . ' AS value FROM ' . $table . ' WHERE '. $keyColumn . ' = 0',
                ReturnType::SINGLE_OBJECT
            );
            if ($zeroKey !== false) {
                $zeroKey->value   = (int)$lastValue->value;
                $lastValue->value = (int)$lastValue->value + 1;
                $this->db->update($table, [$keyColumn], [0], (object)[$keyColumn => $zeroKey->value]);
                if (isset($keyDef['refs'])) {
                    foreach ($keyDef['refs'] as $ref) {
                        $this->db->update($ref, [$keyColumn], [0], (object)[$keyColumn => $zeroKey->value]);
                    }
                }
            }
            $this->execute(
                'ALTER TABLE ' . $table
                . ' CHANGE COLUMN ' . $keyColumn . ' ' . $keyColumn
                . ' INT(10) UNSIGNED NOT NULL AUTO_INCREMENT'
            );
            $this->execute(
                'ALTER TABLE ' . $table . ' AUTO_INCREMENT ' . $lastValue->value
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
