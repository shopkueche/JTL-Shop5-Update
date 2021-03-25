<div class="widget-custom-data widget-visitors">
    {if $oVisitorsInfo->nAll > 0}
        <div class="row mb-3">
            <div class="col-auto mr-auto"><i class="fa fa-users" aria-hidden="true"></i> {__('customers')}: <span class="value">{$oVisitorsInfo->nCustomer}</span></div>
            <div class="col-auto mr-auto"><i class="fa fa-user-secret mr-2" aria-hidden="true"></i> {__('guests')}: <span class="value">{$oVisitorsInfo->nUnknown}</span></div>
            <div class="col-auto text-right">{__('overall')}: <span class="value">{$oVisitorsInfo->nAll}</span></div>
        </div>
    {else}
        <div class="widget-container"><div class="alert alert-info">{__('noVisitorsATM')}</div></div>
    {/if}

    {if is_array($oVisitors_arr) && $oVisitors_arr|@count > 0}
        <table class="table table-border-light table-sm">
            <thead>
                <tr>
                    <th>{__('customer')}</th>
                    <th>{__('info')}</th>
                    <th class="text-center">{__('lastActivity')}</th>
                    <th class="text-right">{__('lastPurchase')}</th>
                </tr>
            </thead>
            <tbody>
            {foreach $oVisitors_arr as $oVisitor}
                {if !empty($oVisitor->kKunde)}
                    <tr>
                        <td class="customer" onclick="$(this).parent().toggleClass('active')">
                            {$oVisitor->cVorname} {$oVisitor->cNachname}
                        </td>
                        <td>
                            {if $oVisitor->cBrowser|strlen > 0}
                                <a href="#" data-toggle="tooltip" data-placement="top" title="{if $oVisitor->dErstellt|strlen > 0}Kunde seit {$oVisitor->dErstellt|date_format:'%d.%m.%Y'}{/if} | Browser: {$oVisitor->cBrowser}{if $oVisitor->cIP|strlen > 0} | IP: {$oVisitor->cIP}{/if}">
                                    <i class="fa fa-user"></i><span class="sr-only">{__('details')}</span>
                                </a>
                            {/if}
                            {if $oVisitor->cEinstiegsseite|strlen > 0}
                                <a href="{$oVisitor->cEinstiegsseite}"  target="_blank" data-toggle="tooltip" data-placement="top" title="{__('entryPage')}: {$oVisitor->cEinstiegsseite}{if $oVisitor->cReferer|strlen > 0} | {__('origin')}: {$oVisitor->cReferer|escape:'html'}{/if}">
                                    <i class="fa fa-globe"></i><span class="sr-only">{__('entryPage')}</span>
                                </a>
                            {/if}
                            {if $oVisitor->cNewsletter === 'Y'}
                                <a href="#" data-toggle="tooltip" data-placement="top" title="Newsletter-Abonnent">
                                    <i class="far fa-envelope"></i><span class="sr-only">{__('newsletterSubscriber')}</span>
                                </a>
                            {/if}
                        </td>
                        <td class="text-muted text-center">
                            {if $oVisitor->dLetzteAktivitaet|strlen > 0}
                                 {if $oVisitor->cAusstiegsseite|strlen > 0}
                                    <a href="{$oVisitor->cAusstiegsseite}" target="_blank" data-toggle="tooltip" data-placement="top" title="{$oVisitor->cAusstiegsseite}">
                                        {$oVisitor->dLetzteAktivitaet|date_format:'%H:%M:%S'}
                                     </a>
                                 {else}
                                    {$oVisitor->dLetzteAktivitaet|date_format:'%H:%M:%S'}
                                 {/if}
                            {/if}
                        </td>
                        <td class="basket text-right">
                            {if $oVisitor->kBestellung > 0}
                                <span title="Letzter Einkauf vom {$oVisitor->dErstellt|date_format:'%d.%m.%Y'}">
                                    <i class="fa fa-shopping-cart" aria-hidden="true"></i> {$oVisitor->fGesamtsumme}
                                </span>
                            {else}
                                <span class="text-muted"><i class="fa fa-shopping-cart" aria-hidden="true"></i> -</span>
                            {/if}
                        </td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
    {/if}
</div>
