<?php

namespace JTL\DB\Services;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;

/**
 * Class GcService
 * @package JTL\DB\Services
 */
class GcService implements GcServiceInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var array
     */
    protected $definition = [
        'tbesucherarchiv'                  => [
            'cDate'     => 'dZeit',
            'cSubTable' => [
                'tbesuchersuchausdruecke' => 'kBesucher'
            ],
            'cInterval' => '180'
        ],
        'tcheckboxlogging'                 => [
            'cDate'     => 'dErstellt',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'texportformatqueuebearbeitet'     => [
            'cDate'     => 'dZuletztGelaufen',
            'cSubTable' => null,
            'cInterval' => '60'
        ],
        'tkampagnevorgang'                 => [
            'cDate'     => 'dErstellt',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'tpreisverlauf'                    => [
            'cDate'     => 'dDate',
            'cSubTable' => null,
            'cInterval' => '120'
        ],
        'tredirectreferer'                 => [
            'cDate'     => 'dDate',
            'cSubTable' => null,
            'cInterval' => '60'
        ],
        'tsitemapreport'                   => [
            'cDate'     => 'dErstellt',
            'cSubTable' => [
                'tsitemapreportfile' => 'kSitemapReport'
            ],
            'cInterval' => '120'
        ],
        'tsuchanfrage'                     => [
            'cDate'     => 'dZuletztGesucht',
            'cSubTable' => [
                'tsuchanfrageerfolglos' => 'cSuche',
                'tsuchanfrageblacklist' => 'cSuche',
                'tsuchanfragencache'    => 'cSuche'
            ],
            'cInterval' => '120'
        ],
        'tsuchcache'                       => [
            'cDate'     => 'dGueltigBis',
            'cSubTable' => [
                'tsuchcachetreffer' => 'kSuchCache'
            ],
            'cInterval' => '30'
        ],
        'tverfuegbarkeitsbenachrichtigung' => [
            'cDate'     => 'dBenachrichtigtAm',
            'cSubTable' => null,
            'cInterval' => '90'
        ]
    ];

    /**
     * GcService constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return $this
     */
    public function run(): GcServiceInterface
    {
        foreach ($this->definition as $table => $mainTables) {
            $dateField = $mainTables['cDate'];
            $subTables = $mainTables['cSubTable'];
            $interval  = $mainTables['cInterval'];
            if ($subTables !== null) {
                $cFrom = $table;
                $cJoin = '';
                foreach ($subTables as $subTable => $cKey) {
                    $cFrom .= ", {$subTable}";
                    $cJoin .= " LEFT JOIN {$subTable} ON {$subTable}.{$cKey} = {$table}.{$cKey}";
                }
                $this->db->query(
                    "DELETE {$cFrom} 
                        FROM {$table} {$cJoin} 
                        WHERE DATE_SUB(NOW(), INTERVAL {$interval} DAY) >= {$table}.{$dateField}",
                    ReturnType::AFFECTED_ROWS
                );
            } else {
                $this->db->query(
                    "DELETE FROM {$table} 
                        WHERE DATE_SUB(NOW(), INTERVAL {$interval} DAY) >= {$dateField}",
                    ReturnType::AFFECTED_ROWS
                );
            }
        }

        return $this;
    }
}
