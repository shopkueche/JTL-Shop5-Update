<script type="text/javascript">
    {literal}
    function confirmDelete(cName) {
        return confirm('{/literal}{__('deleteShippingMethod')}{literal}"' + cName + '"?');
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('shippingmethods') cBeschreibung=__('isleListsHint') cDokuURL=__('shippingmethodsURL')}

<div id="content">
    <div class="dropdown mb-4">
        <button class="btn btn-primary" type="button" id="versandart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="fal fa-plus mr-2"></span>{__('createShippingMethod')}
        </button>
        <div class="dropdown-menu" aria-labelledby="versandart">
            {foreach $versandberechnungen as $versandberechnung}
                <a class="dropdown-item">
                    <form name="versandart_neu" method="post" action="versandarten.php">
                        {$jtl_token}
                        <input type="hidden" name="neu" value="1" />
                        <input type="hidden" id="l{$versandberechnung@index}" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" {if $versandberechnung@index == 0}checked="checked"{/if} />
                        <button type="submit" class="btn btn-link p-0">{$versandberechnung->cName}</button>
                    </form>
                </a>
            {/foreach}
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-responsive table-align-top">
                <thead>
                    <tr>
                        <th>{__('shippingTypeName')}</th>
                        <th>{__('shippingclasses')}</th>
                        <th>{__('customerclass')}</th>
                        <th class="min-w">{__('paymentMethods')}</th>
                        <th class="text-center min-w">{__('shippingPrice')}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                {foreach $versandarten as $versandart}
                    <tr>
                        <td>
                            {$versandart->cName}
                            <hr class="my-1">
                            <span class="d-block shipping-method-country">
                                {foreach $versandart->countries as $country}
                                    {if $country@iteration == 20}
                                        <span class="collapse" aria-expanded="false" id="show-all-countries-{$versandart->kVersandart}">
                                        {$collapse=1}
                                    {/if}
                                    {strip}
                                        <a href="versandarten.php?zuschlag=1&kVersandart={$versandart->kVersandart}&cISO={$country->getISO()}&token={$smarty.session.jtl_token}"
                                            data-toggle="tooltip"
                                            title="{__('isleListsDesc')}">
                                            <span class="small">
                                                {if in_array($country->getISO(), $versandart->shippingSurchargeCountries)}
                                                    <u>{$country->getName()}*</u>
                                                {else}
                                                    {$country->getName()}
                                                {/if}
                                            </span>
                                        </a>
                                    {/strip}{if !$country@last},{/if}
                                    {if $country@iteration > 20 && $country@last}
                                        </span>
                                        <button class="btn btn-link float-right" data-toggle="collapse" data-target="#show-all-countries-{$versandart->kVersandart}">
                                            {__('showAll')} <span class="far fa-chevron-down"></span>
                                        </button>
                                    {/if}
                                {/foreach}
                            </span>
                        </td>
                        <td>
                            <ul class="list-unstyled">
                            {if $versandart->versandklassen|@count == 1 && $versandart->versandklassen[0] === 'Alle'}
                                <li><span class="badge badge-primary text-wrap">{__('all')}</span></li>
                            {else}
                                {foreach $versandart->versandklassen as $versandklasse}
                                    <li><span class="badge badge-primary text-wrap">{$versandklasse}</span></li>
                                {/foreach}
                            {/if}
                            </ul>
                        </td>
                        <td>
                            <ul class="list-unstyled">
                            {foreach $versandart->cKundengruppenName_arr as $cKundengruppenName}
                                <li class="mb-1">{$cKundengruppenName}</li>
                            {/foreach}
                            </ul>
                        </td>
                        <td>
                            <ul class="list-unstyled">
                            {foreach $versandart->versandartzahlungsarten as $zahlungsart}
                                <li class="mb-1">
                                    {$zahlungsart->zahlungsart->cName}
                                    {if isset($zahlungsart->zahlungsart->cAnbieter) && $zahlungsart->zahlungsart->cAnbieter|strlen > 0}
                                        ({$zahlungsart->zahlungsart->cAnbieter})
                                    {/if}
                                    {if $zahlungsart->fAufpreis!=0}
                                        {if $zahlungsart->cAufpreisTyp != "%"}
                                            {getCurrencyConversionSmarty fPreisBrutto=$zahlungsart->fAufpreis bSteuer=false}
                                        {else}
                                            {$zahlungsart->fAufpreis}%
                                        {/if}
                                    {/if}
                                </li>
                            {/foreach}
                            </ul>
                        </td>
                        <td class="text-center">
                            <ul class="list-unstyled">
                            {if $versandart->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl'
                            || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl'
                            || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'}
                                {foreach $versandart->versandartstaffeln as $versandartstaffel}
                                    {if $versandartstaffel->fBis != 999999999}
                                        <li>
                                            {__('upTo')} {$versandartstaffel->fBis} {$versandart->einheit} {$versandartstaffel->fPreis}
                                            {getHelpDesc cDesc="{getCurrencyConversionSmarty fPreisBrutto=$versandartstaffel->fPreis bSteuer=false}"}
                                        </li>
                                    {/if}
                                {/foreach}
                            {elseif $versandart->versandberechnung->cModulId === 'vm_versandkosten_pauschale_jtl'}
                                <li>
                                    {$versandart->fPreis}
                                    {getHelpDesc cDesc="{getCurrencyConversionSmarty fPreisBrutto=$versandart->fPreis bSteuer=false}"}
                                </li>
                            {/if}
                            </ul>
                        </td>
                        <td>
                            <form method="post" action="versandarten.php">
                                {$jtl_token}
                                <div class="btn-group">
                                    <button name="del"
                                            value="{$versandart->kVersandart}"
                                            class="btn btn-link px-2"
                                            onclick="return confirmDelete('{$versandart->cName}');"
                                            title="{__('delete')}"
                                            data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-trash-alt"></span>
                                            <span class="fas fa-trash-alt"></span>
                                        </span>
                                    </button>
                                    <button name="clone"
                                            value="{$versandart->kVersandart}"
                                            class="btn btn-link px-2"
                                            title="{__('duplicate')}"
                                            data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-clone"></span>
                                            <span class="fas fa-clone"></span>
                                        </span>
                                    </button>
                                    <button name="edit"
                                            value="{$versandart->kVersandart}"
                                            class="btn btn-link px-2"
                                            title="{__('edit')}"
                                            data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-edit"></span>
                                            <span class="fas fa-edit"></span>
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
