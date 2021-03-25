<?php

namespace JTL\Backend;

use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * Class JSONAPI
 * @package JTL\Backend
 */
class JSONAPI
{
    /**
     * @var JSONAPI
     */
    private static $instance;

    /**
     * JSONAPI constructor.
     */
    private function __construct()
    {
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @return JSONAPI
     */
    public static function getInstance(): self
    {
        return self::$instance ?? self::$instance = new self();
    }

    /**
     * @param string|array|null $search
     * @param int               $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getSeos($search = null, $limit = 0, $keyName = 'cSeo')
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cSeo'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }
        $items = $this->getItems('tseo', ['cSeo', 'cKey', 'kKey'], null, $searchIn, \ltrim($search, '/'), $limit);

        return $this->itemsToJson($items);
    }

    /**
     * @param string|array|null $search
     * @param int               $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getPages($search = null, $limit = 0, $keyName = 'kLink')
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cName'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems('tlink', ['kLink', 'cName'], null, $searchIn, $search, $limit));
    }

    /**
     * @param string|array|null $search
     * @param int               $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getCategories($search = null, $limit = 0, $keyName = 'kKategorie')
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['tkategorie.cName'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'tkategorie',
            ['tkategorie.kKategorie', 'tkategorie.cName'],
            \CACHING_GROUP_CATEGORY,
            $searchIn,
            $search,
            $limit
        ));
    }

    /**
     * @param string|array|null $search
     * @param int               $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getProducts($search = null, $limit = 0, $keyName = 'kArtikel')
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cName', 'cArtNr'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson(
            $this->getItems(
                'tartikel',
                ['kArtikel', 'cName', 'cArtNr'],
                \CACHING_GROUP_ARTICLE,
                $searchIn,
                $search,
                $limit
            )
        );
    }

    /**
     * @param string|array|null $search
     * @param int               $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getManufacturers($search = null, $limit = 0, $keyName = 'kHersteller')
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cName'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'thersteller',
            ['kHersteller', 'cName'],
            \CACHING_GROUP_MANUFACTURER,
            $searchIn,
            $search,
            $limit
        ));
    }

    /**
     * @param string|array|null $search
     * @param int               $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getCustomers($search = null, $limit = 0, $keyName = 'kKunde')
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cVorname', 'cMail', 'cOrt', 'cPLZ'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        $items         = $this->getItems(
            'tkunde',
            ['kKunde', 'cVorname', 'cNachname', 'cStrasse', 'cHausnummer', 'cPLZ', 'cOrt', 'cMail'],
            null,
            $searchIn,
            $search,
            $limit
        );
        $cryptoService = Shop::Container()->getCryptoService();
        foreach ($items as $item) {
            $item->cNachname = \trim($cryptoService->decryptXTEA($item->cNachname));
            $item->cStrasse  = \trim($cryptoService->decryptXTEA($item->cStrasse));
        }

        return $this->itemsToJson($items);
    }

    /**
     * @param string|array|null $search
     * @param int               $limit
     * @param string            $keyName
     * @return string|bool
     */
    public function getAttributes($search = null, $limit = 0, $keyName = 'kMerkmalWert')
    {
        $searchIn = null;
        if (\is_string($search)) {
            $searchIn = ['cWert'];
        } elseif (\is_array($search)) {
            $searchIn = $keyName;
        }

        return $this->itemsToJson($this->getItems(
            'tmerkmalwertsprache',
            ['kMerkmalWert', 'cWert'],
            \CACHING_GROUP_ARTICLE,
            $searchIn,
            $search,
            $limit
        ));
    }

    /**
     * @param string $table
     * @return bool
     */
    private function validateTableName(string $table): bool
    {
        $res = Shop::Container()->getDB()->queryPrepared(
            "SELECT `table_name` 
                FROM information_schema.tables 
                WHERE `table_type` = 'base table'
                    AND `table_schema` = :sma
                    AND `table_name` = :tn",
            ['sma' => DB_NAME, 'tn' => $table],
            ReturnType::SINGLE_OBJECT
        );

        return $res !== false && $res->table_name === $table;
    }

    /**
     * @param string $table
     * @param array  $columns
     * @return bool
     */
    private function validateColumnNames(string $table, array $columns): bool
    {
        static $tableRows = null;
        if (isset($tableRows[$table])) {
            $rows = $tableRows[$table];
        } else {
            $res  = Shop::Container()->getDB()->queryPrepared(
                'SELECT `column_name` 
                    FROM information_schema.columns 
                    WHERE `table_schema` = :sma
                        AND `table_name` = :tn',
                ['sma' => DB_NAME, 'tn' => $table],
                ReturnType::ARRAY_OF_OBJECTS
            );
            $rows = [];
            foreach ($res as $item) {
                $rows[] = $item->column_name;
                $rows[] = $table . '.' . $item->column_name;
            }

            $tableRows[$table] = $rows;
        }

        return \collect($columns)->every(static function ($e) use ($rows) {
            return \in_array($e, $rows, true);
        });
    }

    /**
     * @param string               $table
     * @param string[]             $columns
     * @param string|null          $addCacheTag
     * @param string[]|string|null $searchIn
     * @param string|string[]|null $searchFor
     * @param int                  $limit
     * @return array
     */
    public function getItems(
        string $table,
        array $columns,
        $addCacheTag = null,
        $searchIn = null,
        $searchFor = null,
        int $limit = 0
    ): array {
        if ($this->validateTableName($table) === false || $this->validateColumnNames($table, $columns) === false) {
            return [];
        }
        $db        = Shop::Container()->getDB();
        $cacheId   = 'jsonapi_' . $table . '_' . $limit . '_';
        $cacheId  .= \md5(\serialize($columns) . \serialize($searchIn) . \serialize($searchFor));
        $cacheTags = [\CACHING_GROUP_CORE];

        if ($addCacheTag !== null) {
            $cacheTags[] = $addCacheTag;
        }

        if (($data = Shop::Container()->getCache()->get($cacheId)) !== false) {
            return $data;
        }

        if (\is_array($searchIn) && \is_string($searchFor)) {
            // full text search
            $searchFor   = $db->escape($searchFor);
            $conditions  = [];
            $colsToCheck = [];

            foreach ($searchIn as $i => $column) {
                $colsToCheck[] = $column;
                $conditions[]  = $column . ' LIKE :val';
            }

            if ($table === 'tkategorie') {
                $qry = 'SELECT ' . \implode(',', $columns) . ', t2.cName AS parentName
                    FROM tkategorie 
                        LEFT JOIN tkategorie AS t2 
                        ON tkategorie.kOberKategorie = t2.kKategorie
                        WHERE ' . \implode(' OR ', $conditions) . ($limit > 0 ? ' LIMIT ' . $limit : '');
            } else {
                $qry = 'SELECT ' . \implode(',', $columns) . '
                        FROM ' . $table . '
                        WHERE ' . \implode(' OR ', $conditions) . ($limit > 0 ? ' LIMIT ' . $limit : '');
            }

            $result = $this->validateColumnNames($table, $colsToCheck)
                ? $db->queryPrepared(
                    $qry,
                    ['val' => '%' . $searchFor . '%'],
                    ReturnType::ARRAY_OF_OBJECTS
                )
                : [];
        } elseif (\is_string($searchIn) && \is_array($searchFor)) {
            // key array select
            $bindValues = [];
            $count      = 1;
            foreach ($searchFor as $t) {
                $bindValues[$count] = $t;
                ++$count;
            }
            $qry    = 'SELECT ' . \implode(',', $columns) . '
                    FROM ' . $table . '
                    WHERE ' . $searchIn . ' IN (' . \implode(',', \array_fill(0, $count - 1, '?')) . ')
                    ' . ($limit > 0 ? 'LIMIT ' . $limit : '');
            $result = $this->validateColumnNames($table, [$searchIn])
                ? $db->queryPrepared(
                    $qry,
                    $bindValues,
                    ReturnType::ARRAY_OF_OBJECTS
                )
                : [];
        } elseif ($searchIn === null && $searchFor === null) {
            // select all
            $result = $db->query(
                'SELECT ' . \implode(',', $columns) . '
                    FROM ' . $table . '
                    ' . ($limit > 0 ? 'LIMIT ' . $limit : ''),
                ReturnType::ARRAY_OF_OBJECTS
            );
        } else {
            // invalid arguments
            $result = [];
        }

        if (!\is_array($result)) {
            $result = [];
        }

        Shop::Container()->getCache()->set($cacheId, $result, $cacheTags);

        return $result;
    }

    /**
     * @param array $items
     * @return string|bool
     */
    public function itemsToJson($items)
    {
        return \json_encode($items);
    }
}
