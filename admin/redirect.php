<?php

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Pagination\DataType;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Redirect;
use JTL\Shop;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('REDIRECT_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$alertHelper  = Shop::Container()->getAlertService();
$errors       = [];
$importResult = handleCsvImportAction('redirects', 'tredirect', [], null, 2, $errors);

if ($importResult > 0) {
    $alertHelper->addAlert(
        Alert::TYPE_ERROR,
        __('errorImport') . '<br><br>' . implode('<br>', $errors),
        'errorImport'
    );
} elseif ($importResult === 0) {
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successImport'), 'successImport');
}

$redirects = $_POST['redirects'] ?? [];

if (Form::validateToken()) {
    switch (Request::verifyGPDataString('action')) {
        case 'save':
            foreach ($redirects as $id => $item) {
                $redirect = new Redirect($id);
                if ($redirect->kRedirect > 0 && $redirect->cToUrl !== $item['cToUrl']) {
                    if (Redirect::checkAvailability($item['cToUrl'])) {
                        $redirect->cToUrl     = $item['cToUrl'];
                        $redirect->cAvailable = 'y';
                        Shop::Container()->getDB()->update('tredirect', 'kRedirect', $redirect->kRedirect, $redirect);
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            sprintf(__('errorURLNotReachable'), $item['cToUrl']),
                            'errorURLNotReachable'
                        );
                    }
                }
            }
            break;
        case 'delete':
            foreach ($redirects as $id => $item) {
                if (isset($item['enabled']) && (int)$item['enabled'] === 1) {
                    Redirect::deleteRedirect($id);
                }
            }
            break;
        case 'delete_all':
            Redirect::deleteUnassigned();
            break;
        case 'new':
            $redirect = new Redirect();
            if ($redirect->saveExt(
                Request::verifyGPDataString('cFromUrl'),
                Request::verifyGPDataString('cToUrl')
            )) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successRedirectSave'), 'successRedirectSave');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCheckInput'), 'errorCheckInput');
                $smarty
                    ->assign('cTab', 'new_redirect')
                    ->assign('cFromUrl', Request::verifyGPDataString('cFromUrl'))
                    ->assign('cToUrl', Request::verifyGPDataString('cToUrl'));
            }
            break;
        default:
            break;
    }
}

$filter = new Filter();
$filter->addTextfield(__('url'), 'cFromUrl', Operation::CONTAINS);
$filter->addTextfield(__('redirectTo'), 'cToUrl', Operation::CONTAINS);
$select = $filter->addSelectfield(__('redirection'), 'cToUrl');
$select->addSelectOption(__('all'), '');
$select->addSelectOption(__('available'), '', Operation::NOT_EQUAL);
$select->addSelectOption(__('missing'), '', Operation::EQUALS);
$filter->addTextfield(__('calls'), 'nCount', Operation::CUSTOM, DataType::NUMBER);
$filter->assemble();

$redirectCount = Redirect::getRedirectCount($filter->getWhereSQL());

$pagination = new Pagination();
$pagination
    ->setItemCount($redirectCount)
    ->setSortByOptions([
        ['cFromUrl', __('url')],
        ['cToUrl', __('redirectTo')],
        ['nCount', __('calls')]
    ])
    ->assemble();

$list = Redirect::getRedirects(
    $filter->getWhereSQL(),
    $pagination->getOrderSQL(),
    $pagination->getLimitSQL()
);

handleCsvExportAction(
    'redirects',
    'redirects.csv',
    static function () use ($filter, $pagination, $redirectCount) {
        $db    = Shop::Container()->getDB();
        $where = $filter->getWhereSQL();
        $order = $pagination->getOrderSQL();

        for ($i = 0; $i < $redirectCount; $i += 1000) {
            $oRedirectIter = $db->query(
                'SELECT cFromUrl, cToUrl
                    FROM tredirect' .
                    ($where !== '' ? ' WHERE ' . $where : '') .
                    ($order !== '' ? ' ORDER BY ' . $order : '') .
                    ' LIMIT ' . $i . ', 1000',
                ReturnType::QUERYSINGLE
            );

            foreach ($oRedirectIter as $oRedirect) {
                yield (object)$oRedirect;
            }
        }
    }
);

$smarty->assign('oFilter', $filter)
       ->assign('pagination', $pagination)
       ->assign('oRedirect_arr', $list)
       ->assign('nTotalRedirectCount', Redirect::getRedirectCount())
       ->display('redirect.tpl');
