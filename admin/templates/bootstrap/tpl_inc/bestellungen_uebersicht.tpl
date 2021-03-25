{include file='tpl_inc/seite_header.tpl' cTitel=__('order') cBeschreibung=__('orderDesc') cDokuURL=__('orderURL')}
<div id="content">
    {if $orders|@count > 0}
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('order')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="search-toolbar mb-3">
                    <form name="bestellungen" method="post" action="bestellungen.php">
                        {$jtl_token}
                        <input type="hidden" name="Suche" value="1" />
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="orderSearch">{__('orderSearchItem')}:</label>
                            <div class="col-sm-auto mb-2">
                                <input class="form-control" name="cSuche" type="text" value="{if isset($cSuche)}{$cSuche}{/if}" id="orderSearch" />
                            </div>
                            <span class="col-sm-auto">
                                <button name="submitSuche" type="submit" class="btn btn-primary btn-block"><i class="fal fa-search"></i></button>
                            </span>
                        </div>
                    </form>
                </div>
                {include file='tpl_inc/pagination.tpl' pagination=$pagination cParam_arr=['cSuche'=>$cSuche]}
                <form name="bestellungen" method="post" action="bestellungen.php">
                    {$jtl_token}
                    <input type="hidden" name="zuruecksetzen" value="1" />
                    {if isset($cSuche) && $cSuche|strlen > 0}
                        <input type="hidden" name="cSuche" value="{$cSuche}" />
                    {/if}
                    <div class="table-responsive">
                        <table class="table table-striped table-align-top">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="text-center">{__('orderNumber')}</th>
                                    <th class="text-left">{__('customer')}</th>
                                    <th class="text-center">{__('orderCostumerRegistered')}</th>
                                    <th class="text-left">{__('orderShippingName')}</th>
                                    <th class="text-left">{__('orderPaymentName')}</th>
                                    <th>{__('orderWawiPickedUp')}</th>
                                    <th class="text-center">{__('status')}</th>
                                    <th>{__('orderSum')}</th>
                                    <th class="text-right">{__('orderDate')}</th>
                                    <th class="text-right">{__('orderIpAddress')}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach $orders as $order}
                                <tr>
                                    <td class="check">
                                        {if $order->cAbgeholt === 'Y' && $order->cZahlungsartName !== 'Amazon Payment' && $order->oKunde !== null}
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="checkbox" name="kBestellung[]" id="order-id-{$order->kBestellung}" value="{$order->kBestellung}" />
                                                <label class="custom-control-label" for="order-id-{$order->kBestellung}"></label>
                                            </div>
                                        {/if}
                                    </td>
                                    <td class="text-center">{$order->cBestellNr}</td>
                                    <td>
                                        {if isset($order->oKunde->cVorname) || isset($order->oKunde->cNachname) || isset($order->oKunde->cFirma)}
                                            {$order->oKunde->cVorname} {$order->oKunde->cNachname}
                                            {if isset($order->oKunde->cFirma) && $order->oKunde->cFirma|strlen > 0}
                                                ({$order->oKunde->cFirma})
                                            {/if}
                                        {else}
                                            {__('noAccount')}
                                        {/if}
                                    </td>
                                    <td class="text-center">{if isset($order->oKunde) && $order->oKunde->nRegistriert === 1}{__('yes')}{else}{__('no')}{/if}</td>
                                    <td>{$order->cVersandartName}</td>
                                    <td>{$order->cZahlungsartName}</td>
                                    <td class="text-center">
                                        {if $order->cAbgeholt === 'Y'}
                                            <i class="fal fa-check text-success"></i>
                                        {else}
                                            <i class="fal fa-times text-danger"></i>
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $order->cStatus == 1}
                                            {__('new')}
                                        {elseif $order->cStatus == 2}
                                            {__('orderInProgress')}
                                        {elseif $order->cStatus == 3}
                                            {__('orderPayed')}
                                        {elseif $order->cStatus == 4}
                                            {__('orderShipped')}
                                        {elseif $order->cStatus == 5}
                                            {__('orderPartlyShipped')}
                                        {elseif $order->cStatus == -1}
                                            {__('orderCanceled')}
                                        {/if}
                                    </td>
                                    <td class="text-center">{$order->WarensummeLocalized[0]}</td>
                                    <td class="text-right">{$order->dErstelldatum_de}</td>
                                    <td class="text-right">{$order->cIP}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <div class="save-wrapper">
                        <div class="row">
                            <div class="col-sm-6 col-xl-auto text-left">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
                                    <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                                </div>
                            </div>
                            <div class="ml-auto col-sm-6 col-xl-auto">
                                <button name="zuruecksetzenBTN" type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-refresh"></i> {__('orderPickedUpResetBTN')}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    {else}
        <div class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noDataAvailable')}</div>
    {/if}
</div>
