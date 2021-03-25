<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\DB\ReturnType;

/**
 * Class AnonymizeIps
 * @package JTL\GeneralDataProtection
 *
 * anonymize IPs in various tables.
 *
 * names of the tables, we manipulate:
 *
 * `tbestellung`
 * `tbesucherarchiv`
 * `tkontakthistory`
 * `tproduktanfragehistory`
 * `tredirectreferer`
 * `tsitemaptracker`
 * `tsuchanfragencache`
 * `tverfuegbarkeitsbenachrichtigung`
 * `tvergleichsliste`
 * `tfloodprotect`
 */
class AnonymizeIps extends Method implements MethodInterface
{
    /**
     * @var array
     */
    private $tablesToUpdate = [
        'tbestellung'                      => [
            'ColKey'     => 'kBestellung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tbesucherarchiv'                  => [
            'ColKey'     => 'kBesucher',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit',
            'ColType'    => 'DATETIME'
        ],
        'tkontakthistory'                  => [
            'ColKey'     => 'kKontaktHistory',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tproduktanfragehistory'           => [
            'ColKey'     => 'kProduktanfrageHistory',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tredirectreferer'                 => [
            'ColKey'     => 'kRedirectReferer',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDate',
            'ColType'    => 'TIMESTAMP'
        ],
        'tsitemaptracker'                  => [
            'ColKey'     => 'kSitemapTracker',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tsuchanfragencache'               => [
            'ColKey'     => 'kSuchanfrageCache',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit',
            'ColType'    => 'DATETIME'
        ],
        'tverfuegbarkeitsbenachrichtigung' => [
            'ColKey'     => 'kVerfuegbarkeitsbenachrichtigung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tvergleichsliste'                 => [
            'ColKey'     => 'kVergleichsliste',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDate',
            'ColType'    => 'DATETIME'
        ],
        'tfloodprotect'                  => [
            'ColKey'     => 'kFloodProtect',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ]
    ];

    /**
     * run all anonymize processes
     */
    public function execute(): void
    {
        $this->anonymizeAllIPs();
    }

    /**
     * anonymize IPs in various tables
     */
    public function anonymizeAllIPs(): void
    {
        $anonymizer = new IpAnonymizer('', true); // anonymize "beautified"
        $ipMaskV4   = $anonymizer->getMaskV4();
        $ipMaskV6   = $anonymizer->getMaskV6();
        $ipMaskV4   = \mb_substr($ipMaskV4, \mb_strpos($ipMaskV4, '.0'), \mb_strlen($ipMaskV4) - 1);
        $ipMaskV6   = \mb_substr($ipMaskV6, \mb_strpos($ipMaskV6, ':0000'), \mb_strlen($ipMaskV6) - 1);
        $dtNow      = $this->now->format('Y-m-d H:i:s');
        foreach ($this->tablesToUpdate as $tableName => $colData) {
            $sql = "SELECT
                    {$colData['ColKey']},
                    {$colData['ColIp']},
                    {$colData['ColCreated']}
                FROM
                    {$tableName}
                WHERE
                    NOT INSTR(cIP, '.*') > 0
                    AND NOT INSTR(cIP, '{$ipMaskV4}') > 0
                    AND NOT INSTR(cIP, '{$ipMaskV6}') > 0";

            if ($colData['ColType'] !== 'TIMESTAMP') {
                $sql .= " AND {$colData['ColCreated']} <= '{$dtNow}' - INTERVAL {$this->interval} DAY";
            } else {
                $sql .= " AND FROM_UNIXTIME({$colData['ColCreated']}) <=
                 '{$dtNow}' - INTERVAL {$this->interval} DAY";
            }

            $sql .= " ORDER BY {$colData['ColCreated']} ASC
                LIMIT {$this->workLimit}";

            $res = $this->db->query(
                $sql,
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($res as $row) {
                try {
                    $row->cIP = $anonymizer->setIp($row->cIP)->anonymize();
                } catch (\Exception $e) {
                    ($this->logger === null) ?: $this->logger->log(\JTLLOG_LEVEL_WARNING, $e->getMessage());
                }
                $szKeyColName = $colData['ColKey'];
                $this->db->update(
                    $tableName,
                    $colData['ColKey'],
                    (int)$row->$szKeyColName,
                    $row
                );
            }
        }
    }
}
