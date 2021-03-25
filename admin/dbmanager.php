<?php

use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Update\DBManager;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('DBCHECK_VIEW', true, true);
$tables = DBManager::getStatus(DB_NAME);
$smarty->assign('tables', $tables);

$restrictedTables = ['tadminlogin', 'tbrocken', 'tsession', 'tsynclogin'];

/**
 * @param string $query
 * @return array|int|object
 */
function exec_query($query)
{
    try {
        Shop::Container()->getDB()->beginTransaction();
        $result = Shop::Container()->getDB()->executeQuery($query, 9);
        Shop::Container()->getDB()->commit();

        return $result;
    } catch (PDOException $e) {
        Shop::Container()->getDB()->rollback();
        throw $e;
    }
}

$jsTypo = (object)['tables' => []];
foreach ($tables as $table => $info) {
    $columns                = DBManager::getColumns($table);
    $columns                = array_map(
        static function ($n) {
            return null;
        },
        $columns
    );
    $jsTypo->tables[$table] = $columns;
}
$smarty->assign('jsTypo', $jsTypo);

switch (true) {
    case isset($_GET['table']):
        $table   = $_GET['table'];
        $status  = DBManager::getStatus(DB_NAME, $table);
        $columns = DBManager::getColumns($table);
        $indexes = DBManager::getIndexes($table);

        $smarty->assign('selectedTable', $table)
            ->assign('status', $status)
            ->assign('columns', $columns)
            ->assign('indexes', $indexes)
            ->assign('sub', 'table')
            ->display('dbmanager.tpl');
        break;

    case isset($_GET['select']):
        $table = $_GET['select'];

        if (!preg_match('/^\w+$/i', $table, $m) || !Form::validateToken()) {
            die('Not allowed.');
        }

        $status  = DBManager::getStatus(DB_NAME, $table);
        $columns = DBManager::getColumns($table);
        $indexes = DBManager::getIndexes($table);

        $defaultFilter = [
            'limit'  => 50,
            'offset' => 0,
            'where'  => []
        ];
        $filter        = $_GET['filter'] ?? [];
        $filter        = array_merge($defaultFilter, $filter);
        // validate filter
        $filter['limit'] = (int)$filter['limit'];
        $page            = Request::getInt('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        if ($filter['limit'] < 1) {
            $filter['limit'] = 1;
        }

        $filter['offset'] = ($page - 1) * $filter['limit'];

        $baseQuery = 'SELECT * FROM ' . $table;
        // query parts
        $queryParams = [];
        $queryParts  = ['select' => $baseQuery];
        // where
        if (isset($filter['where']['col'])) {
            $whereParts  = [];
            $columnCount = count($filter['where']['col']);
            for ($i = 0; $i < $columnCount; $i++) {
                if (!empty($filter['where']['col'][$i]) && !empty($filter['where']['op'][$i])) {
                    $col = $filter['where']['col'][$i];
                    $val = $filter['where']['val'][$i];
                    $op  = mb_convert_case($filter['where']['op'][$i], MB_CASE_UPPER);
                    if ($op === 'LIKE %%') {
                        $op  = 'LIKE';
                        $val = sprintf('%%%s%%', trim($val, '%'));
                    }
                    $whereParts[]                  = sprintf('`%s` %s :where_%d_val', $col, $op, $i);
                    $queryParams["where_{$i}_val"] = $val;
                }
            }
            if (count($whereParts) > 0) {
                $queryParts['where'] = 'WHERE ' . implode(' AND ', $whereParts);
            }
        }
        // count without limit
        $query = implode(' ', $queryParts);
        $count = Shop::Container()->getDB()->queryPrepared($query, $queryParams, ReturnType::AFFECTED_ROWS);
        $pages = (int)ceil($count / $filter['limit']);
        // limit
        $queryParams['limit_count']  = $filter['limit'];
        $queryParams['limit_offset'] = $filter['offset'];
        $queryParts['limit']         = 'LIMIT :limit_offset, :limit_count';

        $query = implode(' ', $queryParts);
        $info  = null;
        $data  = Shop::Container()->getDB()->queryPrepared(
            $query,
            $queryParams,
            ReturnType::ARRAY_OF_ASSOC_ARRAYS,
            false,
            static function ($o) use (&$info) {
                $info = $o;
            }
        );

        $smarty->assign('selectedTable', $table)
            ->assign('data', $data)
            ->assign('page', $page)
            ->assign('query', $query)
            ->assign('count', $count)
            ->assign('pages', $pages)
            ->assign('filter', $filter)
            ->assign('columns', $columns)
            ->assign('info', $info)
            ->assign('sub', 'select')
            ->display('dbmanager.tpl');
        break;

    case isset($_GET['command']):
        $command = $_GET['command'];
        $query   = null;
        if (isset($_POST['query'])) {
            $query = $_POST['query'];
        } elseif (isset($_POST['sql_query_edit'])) {
            $query = $_POST['sql_query_edit'];
        }
        if ($query !== null && Form::validateToken()) {
            try {
                $parser = new SqlParser\Parser($query);
                if (is_array($parser->errors) && count($parser->errors) > 0) {
                    throw $parser->errors[0];
                }
                $q = SqlParser\Utils\Query::getAll($query);
                if ($q['is_select'] !== true) {
                    throw new Exception(sprintf('Query is restricted to SELECT statements'));
                }
                foreach ($q['select_tables'] as $t) {
                    [$table, $dbname] = $t;
                    if ($dbname !== null && strcasecmp($dbname, DB_NAME) !== 0) {
                        throw new Exception(sprintf('Well, at least u tried :)'));
                    }
                    if (in_array(mb_convert_case($table, MB_CASE_LOWER), $restrictedTables, true)) {
                        throw new Exception(sprintf('Permission denied for table `%s`', $table));
                    }
                }
                $stmt = $q['statement'];
                if ($q['limit'] === false) {
                    $stmt->limit = new SqlParser\Components\Limit(50, 0);
                }
                $newQuery = $stmt->build();
                $query    = SqlParser\Utils\Formatter::format($newQuery, ['type' => 'text']);
                $result   = exec_query($newQuery);
                $smarty->assign('result', $result);
            } catch (Exception $e) {
                $smarty->assign('error', $e);
            }

            $smarty->assign('query', $query);
        } elseif (isset($_GET['query'])) {
            $smarty->assign('query', $_GET['query']);
        }

        $smarty->assign('sub', 'command')
            ->display('dbmanager.tpl');
        break;

    default:
        $definedTables = array_keys(getDBFileStruct() ?: []);

        $smarty->assign('definedTables', $definedTables)
            ->assign('sub', 'default')
            ->display('dbmanager.tpl');
        break;
}
