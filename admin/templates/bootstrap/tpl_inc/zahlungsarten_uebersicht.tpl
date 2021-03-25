{include file='tpl_inc/seite_header.tpl' cTitel=__('paymentmethods') cBeschreibung=__('installedPaymentmethods') cDokuURL=__('paymentmethodsURL')}
<div id="content" class="row">
    <div class="{if $recommendations->getRecommendations()->isNotEmpty()}col-md-7{else}col-lg-9 col-xl-7{/if}">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-content-center">
                    <thead>
                    <tr>
                        <th>{__('installedPaymentTypes')}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $zahlungsarten as $zahlungsart}
                        <tr class="text-vcenter">
                            <td>
                                {if $zahlungsart->nActive == 1}
                                    <span class="text-success" title="{__('active')}"><i class="fal fa-check text-success"></i></span>
                                {else}
                                    <span class="text-danger" title="{__('inactive')}">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </span>
                                {/if}
                                <span class="ml-2">{$zahlungsart->cName}
                                    <small>{$zahlungsart->cAnbieter}</small>
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <a href="zahlungsarten.php?a=log&kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"
                                       class="btn btn-link sx-2 down
                                                  {if $zahlungsart->nLogCount > 0}
                                                        {if $zahlungsart->nErrorLogCount}text-danger{/if}
                                                  {else}
                                                        text-success disabled
                                                  {/if}"
                                       title="{__('viewLog')}"
                                       data-toggle="tooltip">
                                        <span class="icon-hover">
                                            {if $zahlungsart->nLogCount > 0}
                                                {if $zahlungsart->nErrorLogCount}
                                                    <span class="fal fa-exclamation-triangle"></span>
                                                    <span class="fas fa-exclamation-triangle"></span>
                                                {else}
                                                    <span class="fal fa-bars"></span>
                                                    <span class="fas fa-bars"></span>
                                                {/if}
                                            {else}
                                                <span class="fal fa-check"></span>
                                                <span class="fas fa-check"></span>
                                            {/if}
                                        </span>
                                    </a>
                                    <a {if $zahlungsart->nEingangAnzahl > 0}href="zahlungsarten.php?a=payments&kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"{/if}
                                       class="btn btn-link sx-2 {if $zahlungsart->nEingangAnzahl === 0}disabled{/if}"
                                       title="{__('paymentsReceived')}"
                                       data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-hand-holding-usd"></span>
                                            <span class="fas fa-hand-holding-usd"></span>
                                        </span>
                                    </a>
                                    {if $zahlungsart->markedForDelete}
                                        <a href="zahlungsarten.php?a=del&kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"
                                           class="btn btn-link sx-2"
                                           title="{__('delete')}"
                                           data-toggle="tooltip">
                                            <span class="icon-hover">
                                                <span class="fal fa-trash"></span>
                                                <span class="fas fa-trash"></span>
                                            </span>
                                        </a>
                                    {else}
                                        <a href="zahlungsarten.php?kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"
                                           class="btn btn-link sx-2"
                                           title="{__('edit')}"
                                           data-toggle="tooltip">
                                            <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </a>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="card-footer save-wrapper">
                <form method="post" action="zahlungsarten.php" class="top">
                    {$jtl_token}
                    <input type="hidden" name="checkNutzbar" value="1"/>
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="checkSubmit" type="submit" title="{__('paymentmethodsCheckAll')}" class="btn btn-outline-primary">
                                <i class="fa fa-refresh"></i> {__('paymentmethodsCheckAll')}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {if $recommendations->getRecommendations()->isNotEmpty()}
    <div class="col-md-5">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th colspan="2">{__('weRecommend')}</th>
                        </tr>
                        </thead>
                        <tbody>
                            {foreach $recommendations->getRecommendations() as $recommendation}
                                <tr>
                                    <td><img src="{$recommendation->getPreviewImage()}" style="max-width: 120px;" alt="{$recommendation->getTitle()}" loading="lazy"></td>
                                    <td>
                                        <p>{$recommendation->getTeaser()}</p>
                                        <a href="premiumplugin.php?scope={$recommendations->getScope()}&id={$recommendation->getId()}"
                                           class="btn btn-primary">
                                            {__('getToKnowMore')}
                                            <span class="fal fa-long-arrow-right ml-1"></span>
                                        </a>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>