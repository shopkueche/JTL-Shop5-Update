<form method="post" action="zusatzverpackung.php">
    {$jtl_token}
    {if $packagings|@count > 0}
    <div class="card">
        <div class="table-responsive card-body">
            {include file='tpl_inc/pagination.tpl' pagination=$pagination}
            <table class="list table table-striped table-align-top">
                <thead>
                <tr>
                    <th class="th-1"></th>
                    <th class="th-2">{__('name')}</th>
                    <th class="th-3">{__('price')}</th>
                    <th class="th-4">{__('minOrderValue')}</th>
                    <th class="th-5">{__('zusatzverpackungExemptFromCharge')}</th>
                    <th class="th-6">{__('customerGroup')}</th>
                    <th class="th-7 text-center">{__('active')}</th>
                    <th class="th-8">&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                {foreach $packagings as $packaging}
                    <tr>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" id="kVerpackung-{$packaging->kVerpackung}" type="checkbox" name="kVerpackung[]" value="{$packaging->kVerpackung}">
                                <label class="custom-control-label" for="kVerpackung-{$packaging->kVerpackung}"></label>
                            </div>
                        </td>
                        <td><label for="kVerpackung-{$packaging->kVerpackung}">{$packaging->cName}</label></td>
                        <td>{getCurrencyConversionSmarty fPreisBrutto=$packaging->fBrutto}</td>
                        <td>{getCurrencyConversionSmarty fPreisBrutto=$packaging->fMindestbestellwert}</td>
                        <td>{getCurrencyConversionSmarty fPreisBrutto=$packaging->fKostenfrei}</td>
                        <td>
                            {foreach $packaging->cKundengruppe_arr as $cKundengruppe}
                                {$cKundengruppe}{if !$cKundengruppe@last},{/if}
                            {/foreach}
                        </td>
                        <td class="text-center">
                            <input name="nAktivTMP[]" type="hidden" value="{$packaging->kVerpackung}" checked>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="nAktiv[]" type="checkbox" id="active-id-{$packaging->kVerpackung}" value="{$packaging->kVerpackung}"{if $packaging->nAktiv == 1} checked{/if}>
                                <label class="custom-control-label" for="active-id-{$packaging->kVerpackung}"></label>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="zusatzverpackung.php?kVerpackung={$packaging->kVerpackung}&token={$smarty.session.jtl_token}"
                                   class="btn btn-link px-2"
                                   title="{__('modify')}"
                                   data-toggle="tooltip">
                                    <span class="icon-hover">
                                        <span class="fal fa-edit"></span>
                                        <span class="fas fa-edit"></span>
                                    </span>
                                </a>
                            </div>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
        {else}
        <div class="alert alert-info">{__('zusatzverpackungAddedNone')}</div>
        {/if}
        <div class="card-footer save-wrapper">
            <div class="row">
                {if $packagings|@count > 0}
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-block">
                            <i class="fas fa-trash-alt"></i> {__('delete')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button name="action" type="submit" value="refresh" class="btn btn-outline-primary btn-block">
                            <i class="fa fa-refresh"></i> {__('update')}
                        </button>
                    </div>
                {/if}
                <div class="{if $packagings|@count === 0}ml-auto{/if} col-sm-6 col-xl-auto">
                    <a href="zusatzverpackung.php?kVerpackung=0&token={$smarty.session.jtl_token}"
                       class="btn btn-primary btn-block" title="{__('modify')}">
                        <i class="fa fa-share"></i> {__('zusatzverpackungCreate')}
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
