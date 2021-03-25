{include file='tpl_inc/seite_header.tpl' cTitel=__('coupons') cBeschreibung=__('couponsDesc') cDokuURL=__('couponsURL')}
{include file='tpl_inc/sortcontrols.tpl'}

{function kupons_uebersicht_tab}
    <div id="{$cKuponTyp}" class="tab-pane fade{if $tab === $cKuponTyp} active show{/if}">
        <div>
            {if $nKuponCount > 0}
                {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter cParam_arr=['tab'=>$cKuponTyp]}
            {/if}
            {if $oKupon_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' pagination=$pagination cParam_arr=['tab'=>$cKuponTyp]}
            {/if}
            <form method="post" action="kupons.php">
                {$jtl_token}
                <input type="hidden" name="cKuponTyp" id="cKuponTyp_{$cKuponTyp}" value="{$cKuponTyp}">
                {if $oKupon_arr|@count > 0}
                    <div class="table-responsive">
                        <table class="list table table-align-top">
                            <thead>
                                <tr>
                                    <th title="{__('active')}"></th>
                                    <th></th>
                                    <th>{__('name')} {call sortControls pagination=$pagination nSortBy=0}</th>
                                    {if $cKuponTyp === $couponTypes.standard || $cKuponTyp === $couponTypes.newCustomer}<th>{__('value')}</th>{/if}
                                    {if $cKuponTyp === $couponTypes.standard || $cKuponTyp === $couponTypes.shipping}
                                        <th>{__('code')} {call sortControls pagination=$pagination nSortBy=1}</th>
                                    {/if}
                                    <th class="text-center">{__('mbw')}</th>
                                    <th class="text-center">{__('curmaxusage')} {call sortControls pagination=$pagination nSortBy=2}</th>
                                    <th>{__('restrictions')}</th>
                                    <th>{__('validityPeriod')}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $oKupon_arr as $oKupon}
                                    <tr{if $oKupon->cAktiv === 'N'} class="text-danger"{/if}>
                                        <td>{if $oKupon->cAktiv === 'N'}<i class="fal fa-times"></i>{/if}</td>
                                        <td>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="checkbox" name="kKupon_arr[]" id="kupon-{$oKupon->kKupon}" value="{$oKupon->kKupon}">
                                                <label class="custom-control-label" for="kupon-{$oKupon->kKupon}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <label for="kupon-{$oKupon->kKupon}">
                                                {$oKupon->cName}
                                            </label>
                                        </td>
                                        {if $cKuponTyp === $couponTypes.standard || $cKuponTyp === $couponTypes.newCustomer}
                                            <td>
                                                {if $oKupon->cWertTyp === 'festpreis'}
                                                    <span data-toggle="tooltip" data-placement="right" data-html="true"
                                                          title='{getCurrencyConversionSmarty fPreisBrutto=$oKupon->fWert}'>
                                                        {$oKupon->cLocalizedValue}
                                                    </span>
                                                {else}
                                                    {$oKupon->fWert} %
                                                {/if}
                                            </td>
                                        {/if}
                                        {if $cKuponTyp === $couponTypes.standard || $cKuponTyp === $couponTypes.shipping}<td>{$oKupon->cCode}</td>{/if}
                                        <td class="text-center">
                                            <span data-toggle="tooltip" data-placement="right" data-html="true"
                                                  title='{getCurrencyConversionSmarty fPreisBrutto=$oKupon->fMindestbestellwert}'>
                                                {$oKupon->cLocalizedMbw}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            {$oKupon->nVerwendungenBisher}
                                            {if $oKupon->nVerwendungen > 0}
                                            {__('of')} {$oKupon->nVerwendungen}</td>
                                            {/if}
                                        <td>
                                            {if !empty({$oKupon->cKundengruppe})}
                                                {__('only')} {$oKupon->cKundengruppe}<br>
                                            {/if}
                                            {if !empty({$oKupon->cArtikelInfo})}
                                                {$oKupon->cArtikelInfo} {__('products')}<br>
                                            {/if}
                                            {if !empty({$oKupon->cHerstellerInfo})}
                                                {$oKupon->cHerstellerInfo} {__('manufacturers')}<br>
                                            {/if}
                                            {if !empty({$oKupon->cKategorieInfo})}
                                                {$oKupon->cKategorieInfo} {__('categories')}<br>
                                            {/if}
                                            {if !empty({$oKupon->cKundenInfo})}
                                                {$oKupon->cKundenInfo} {__('customers')}<br>
                                            {/if}
                                        </td>
                                        <td>
                                            {__('from')}: {$oKupon->cGueltigAbShort}<br>
                                            {__('to')}: {$oKupon->cGueltigBisShort}
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="kupons.php?kKupon={$oKupon->kKupon}&token={$smarty.session.jtl_token}"
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
                {elseif $nKuponCount > 0}
                    <div class="alert alert-info" role="alert">{__('noFilterResults')}</div>
                {else}
                    <div class="alert alert-info" role="alert">
                        {__('emptySetMessage1')} {__($cKuponTypName)} {__('emptySetMessage2')}
                    </div>
                {/if}
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="col-sm-6 col-xl-auto text-left">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="ALLMSGS" id="ALLMSGS_{$cKuponTyp}" onclick="AllMessages(this.form);">
                                <label class="custom-control-label" for="ALLMSGS_{$cKuponTyp}">{__('globalSelectAll')}</label>
                            </div>
                        </div>
                        {if $oKupon_arr|@count > 0}
                            <div class="ml-auto col-sm-6 col-xl-auto">
                                <button type="submit" class="btn btn-danger btn-block" name="action" value="loeschen">
                                    <i class="fas fa-trash-alt"></i> {__('delete')}
                                </button>
                            </div>
                            <div class="col-sm-6 col-xl-auto">
                                {include file='tpl_inc/csv_export_btn.tpl' exporterId=$cKuponTyp}
                            </div>
                        {/if}
                        <div class="{if !$oKupon_arr|@count > 0}ml-auto{/if} col-sm-6 col-xl-auto">
                            {include file='tpl_inc/csv_import_btn.tpl' importerId="kupon_{$cKuponTyp}" importerType="kupon"}
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <a href="kupons.php?kKupon=0&cKuponTyp={$cKuponTyp}&token={$smarty.session.jtl_token}"
                               class="btn btn-primary btn-block" title="{__('modify')}">
                                <i class="fa fa-share"></i> {__($cKuponTypName|cat:'Create')}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/function}

<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $tab === $couponTypes.standard} active{/if}" data-toggle="tab" role="tab" href="#{$couponTypes.standard}" aria-expanded="false">
                        {__('standardCoupon')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $tab === $couponTypes.shipping} active{/if}" data-toggle="tab" role="tab" href="#{$couponTypes.shipping}" aria-expanded="false">
                        {__('shippingCoupon')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $tab === $couponTypes.newCustomer} active{/if}" data-toggle="tab" role="tab" href="#{$couponTypes.newCustomer}" aria-expanded="false">
                        {__('newCustomerCoupon')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            {kupons_uebersicht_tab
                cKuponTyp=$couponTypes.standard
                cKuponTypName='standardCoupon'
                oKupon_arr=$oKuponStandard_arr
                nKuponCount=$nKuponStandardCount
                pagination=$oPaginationStandard
                oFilter=$oFilterStandard
            }
            {kupons_uebersicht_tab
                cKuponTyp=$couponTypes.shipping
                cKuponTypName='shippingCoupon'
                oKupon_arr=$oKuponVersandkupon_arr
                nKuponCount=$nKuponVersandCount
                pagination=$oPaginationVersandkupon
                oFilter=$oFilterVersand
            }
            {kupons_uebersicht_tab
                cKuponTyp=$couponTypes.newCustomer
                cKuponTypName='newCustomerCoupon'
                oKupon_arr=$oKuponNeukundenkupon_arr
                nKuponCount=$nKuponNeukundenCount
                pagination=$oPaginationNeukundenkupon
                oFilter=$oFilterNeukunden
            }
        </div>
    </div>
</div>
