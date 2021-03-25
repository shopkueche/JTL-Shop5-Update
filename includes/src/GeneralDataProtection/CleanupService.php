<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\DB\ReturnType;

/**
 * Class CleanupService
 * @package JTL\GeneralDataProtection
 */
class CleanupService extends Method implements MethodInterface
{
    /**
     * @var array
     */
    protected $definition = [
        'tbesucherarchiv'              => [
            'cDate'     => 'dZeit',
            'cDateType' => 'DATETIME',
            'cSubTable' => [
                'tbesuchersuchausdruecke' => 'kBesucher'
            ],
            'cInterval' => '2920' // anonymized after 7 days, removed after 8 years (former 180 days)
        ],
        'tcheckboxlogging'             => [
            'cDate'     => 'dErstellt',
            'cDateType' => 'DATETIME',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'texportformatqueuebearbeitet' => [
            'cDate'     => 'dZuletztGelaufen',
            'cDateType' => 'DATETIME',
            'cSubTable' => null,
            'cInterval' => '30'
        ],
        'tkampagnevorgang'             => [
            'cDate'     => 'dErstellt',
            'cDateType' => 'DATETIME',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'tpreisverlauf'                => [
            'cDate'     => 'dDate',
            'cDateType' => 'DATE',
            'cSubTable' => null,
            'cInterval' => '730' // 2 years (former 120 days)
        ],
        'tredirectreferer'             => [
            'cDate'     => 'dDate',
            'cDateType' => 'TIMESTAMP',
            'cSubTable' => null,
            'cInterval' => '2920' // anonymized after 7 days, removed after 8 years (former 60 days)
        ],
        'tsitemapreport'               => [
            'cDate'     => 'dErstellt',
            'cDateType' => 'DATETIME',
            'cSubTable' => [
                'tsitemapreportfile' => 'kSitemapReport'
            ],
            'cInterval' => '365' // (former 120 days)
        ],
        'tsuchanfrage'                 => [
            'cDate'     => 'dZuletztGesucht',
            'cDateType' => 'DATETIME',
            'cSubTable' => [
                'tsuchanfrageerfolglos' => 'cSuche',
                'tsuchanfrageblacklist' => 'cSuche',
                'tsuchanfragencache'    => 'cSuche' // (anonymized after 7 days)
            ],
            'cInterval' => '2920' // anonymized after 7 days, removed after 8 years (former 60 days)
        ],
        'tsuchcache'                   => [
            'cDate'     => 'dGueltigBis',
            'cDateType' => 'DATETIME',
            'cSubTable' => [
                'tsuchcachetreffer' => 'kSuchCache'
            ],
            'cInterval' => '30'
        ],
        'tfsession'                    => [
            'cDate'     => 'dErstellt',
            'cDateType' => 'DATETIME',
            'cSubTable' => null,
            'cInterval' => '7'
        ]
    ];

    /**
     * remove data from various tables
     */
    public function execute(): void
    {
        foreach ($this->definition as $table => $tableData) {
            $dateField  = $tableData['cDate'];
            $subTables  = $tableData['cSubTable'];
            $cInterval  = $tableData['cInterval'];
            $cObjectNow = $this->now->format('Y-m-d H:i:s');
            if ($subTables !== null) {
                $from = $table;
                $join = '';
                foreach ($subTables as $cSubTable => $cKey) {
                    $from .= ', ' . $cSubTable;
                    $join .= ' LEFT JOIN ' . $cSubTable .
                        ' ON ' . $cSubTable . '.' . $cKey . ' = ' . $table . '.' . $cKey;
                }
                $dateCol = $table . '.' . $dateField;
                if ($tableData['cDateType'] === 'TIMESTAMP') {
                    $dateCol = 'FROM_UNIXTIME(' . $dateCol . ')';
                }
                $this->db->query(
                    'DELETE ' . $from . '
                        FROM ' . $table . $join . "
                        WHERE DATE_SUB('" . $cObjectNow . "', INTERVAL " . $cInterval . ' DAY) >= ' . $dateCol,
                    ReturnType::DEFAULT
                );
            } else {
                $dateCol = $dateField;
                if ($tableData['cDateType'] === 'TIMESTAMP') {
                    $dateCol = 'FROM_UNIXTIME(' . $dateCol . ')';
                }
                $this->db->query(
                    'DELETE FROM ' . $table . "
                        WHERE DATE_SUB('" . $cObjectNow . "', INTERVAL " . $cInterval . ' DAY) >= ' . $dateCol,
                    ReturnType::DEFAULT
                );
            }
        }
    }
}
