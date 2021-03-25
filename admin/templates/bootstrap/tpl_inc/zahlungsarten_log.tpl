{include file='tpl_inc/seite_header.tpl' cTitel=__('paymentmethods') cBeschreibung=$paymentData->cName cDokuURL=__('paymentmethodsURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/filtertools.tpl' oFilter=$filterStandard cParam_arr=['a'=>'log',
            'token'=>$smarty.session.jtl_token, 'kZahlungsart'=>$paymentData->kZahlungsart]}
            {if !empty($paymentLogs)}
                {include file='tpl_inc/pagination.tpl' pagination=$paginationPaymentLog cParam_arr=['a'=>'log',
                'token'=>$smarty.session.jtl_token, 'kZahlungsart'=>$paymentData->kZahlungsart]}
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{__('note')}</th>
                                <th>{__('date')}</th>
                                <th>{__('level')}</th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach $paymentLogs as $log}
                            <tr>
                                <td>{$log->cLog}</td>
                                <td>
                                    <small class="text-muted">{$log->dDatum}</small>
                                </td>
                                <td>
                                    {if $log->nLevel == 1}
                                        <span class="label text-danger logError">{__('logError')}</span>
                                    {elseif $log->nLevel == 2}
                                        <span class="label text-info logNotice">{__('logNotice')}</span>
                                    {else}
                                        <span class="label text-default logDebug">{__('logDebug')}</span>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-lg-auto">
                            <a href="zahlungsarten.php" class="btn btn-outline-primary btn-block">
                                <i class="fa fa-angle-double-left"></i> {__('goBack')}
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-auto">
                            <button class="btn btn-danger reset btn-block" data-toggle="modal" data-target="#reset-payment-modal" data-href="zahlungsarten.php?a=logreset&kZahlungsart={$paymentData->kZahlungsart}&token={$smarty.session.jtl_token}">
                                <i class="fas fa-trash-alt"></i> {__('logReset')}
                            </button>
                        </div>
                    </div>
                </div>
            {else}
                <div class="alert alert-info">
                    <p>{__('noLogs')}</p>
                </div>
                <a href="zahlungsarten.php" class="btn btn-outline-primary"><i class="fa fa-angle-double-left"></i> {__('goBack')}</a>
            {/if}
        </div>
    </div>
</div>
{include file='tpl_inc/modal_confirm.tpl' modalTitle=$paymentData->cName|cat:' '|cat:__('logReset') modalID='reset-payment'}

