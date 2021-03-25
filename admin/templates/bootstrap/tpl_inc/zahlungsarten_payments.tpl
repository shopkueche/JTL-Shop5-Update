{include file='tpl_inc/seite_header.tpl' cTitel=__('payments')|cat:$oZahlungsart->cName cBeschreibung=__('paymentsDesc') cDokuURL=__('paymentsURL')}
<div id="content">
    {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter cParam_arr=['a'=>'payments',
        'token'=>$smarty.session.jtl_token, 'kZahlungsart'=>$oZahlungsart->kZahlungsart]}
    {include file='tpl_inc/pagination.tpl' pagination=$pagination cParam_arr=['a'=>'payments',
        'token'=>$smarty.session.jtl_token, 'kZahlungsart'=>$oZahlungsart->kZahlungsart]}
    <form method="post" action="zahlungsarten.php">
        {$jtl_token}
        <input type="hidden" name="a" value="payments" />
        <input type="hidden" name="kZahlungsart" value="{$oZahlungsart->kZahlungsart}" />
        <div class="card">
            {if $oZahlunseingang_arr|@count > 0}
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{__('date')}/{__('time')}</th>
                                <th>{__('orderNumberShort')}</th>
                                <th>{__('customer')}</th>
                                <th>{__('amountPayed')}</th>
                                <th>{__('paymentDue')}</th>
                                <th>{__('currency')}</th>
                                <th>{__('syncedWithWawi')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $oZahlunseingang_arr as $oZahlungseingang}
                                <tr>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" name="kEingang_arr[]"
                                               id="eingang-{$oZahlungseingang->kZahlungseingang}"
                                               value="{$oZahlungseingang->kZahlungseingang}">
                                            <label class="custom-control-label" for="eingang-{$oZahlungseingang->kZahlungseingang}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <label for="eingang-{$oZahlungseingang->kZahlungseingang}">{$oZahlungseingang->dZeit}</label>
                                    </td>
                                    <td>{$oZahlungseingang->cBestellNr}</td>
                                    <td>
                                        {$oZahlungseingang->cVorname} {$oZahlungseingang->cNachname}<br>
                                        {if !empty($oZahlungseingang->cZahler)}&lt;{$oZahlungseingang->cZahler}&gt;{/if}
                                    </td>
                                    <td>
                                        {$oZahlungseingang->fBetrag|number_format:2:',':'.'}
                                    </td>
                                    <td>
                                        {$oZahlungseingang->fZahlungsgebuehr|number_format:2:',':'.'}
                                    </td>
                                    <td>{$oZahlungseingang->cISO}</td>
                                    <td>
                                        {if $oZahlungseingang->cAbgeholt === 'Y'}
                                            <span class="label label-success" title="Aktiv"><i class="fal fa-check text-success fa-fw"></i></span>
                                        {elseif $oZahlungseingang->cAbgeholt === 'N'}
                                            <span class="label label-danger" title="Inaktiv"><i class="fal fa-times fa-fw"></i></span>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" name="ALLMSGS" id="ALLMSGS" onclick="AllMessages(this.form);">
                                        <label class="custom-control-label" for="ALLMSGS"></label>
                                    </div>
                                </td>
                                <td colspan="7"><label for="ALLMSGS">{__('selectAllShown')}</label></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">
                    {__('noDataAvailable')}
                </div>
            {/if}
            <div class="card-footer">
                <div class="btn-group">
                    <button type="submit" name="action" value="paymentwawireset" class="btn btn-danger">
                        <i class="fa fa-refresh"></i>
                        {__('wawiSyncReset')}
                    </button>
                    <a class="btn btn-primary" href="zahlungsarten.php">{__('goBack')}</a>
                </div>
            </div>
        </div>
    </form>
</div>
