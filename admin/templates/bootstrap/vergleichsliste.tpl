{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('configureComparelist') cBeschreibung=__('configureComparelistDesc') cDokuURL=__('configureComparelistURL')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'letztenvergleiche'} active{/if}" data-toggle="tab" role="tab" href="#letztenvergleiche">
                        {__('last20Compares')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'topartikel'} active{/if}" data-toggle="tab" role="tab" href="#topartikel">
                        {__('topCompareProducts')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                        {__('compareSettings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="letztenvergleiche" class="tab-pane fade {if $cTab === '' || $cTab === 'letztenvergleiche'} active show{/if}">
                {if $Letzten20Vergleiche && $Letzten20Vergleiche|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination cAnchor='letztenvergleiche'}
                    <div class="settings table-responsive">
                        <table class="table table-striped table-align-top">
                            <thead>
                                <tr>
                                    <th class="th-1 text-center">{__('compareID')}</th>
                                    <th class="text-left">{__('compareProducts')}</th>
                                    <th class="th-3 text-center">{__('compareDate')}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach $Letzten20Vergleiche as $oVergleichsliste20}
                                <tr>
                                    <td class="text-center">{$oVergleichsliste20->kVergleichsliste}</td>
                                    <td class="">
                                        {foreach $oVergleichsliste20->oLetzten20VergleichslistePos_arr as $oVergleichslistePos20}
                                            <a href="{$shopURL}/index.php?a={$oVergleichslistePos20->kArtikel}" target="_blank">{$oVergleichslistePos20->cArtikelName}</a>{if !$oVergleichslistePos20@last}{/if}
                                            <br />
                                        {/foreach}
                                    </td>
                                    <td class="text-center">{$oVergleichsliste20->Datum}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="topartikel" class="tab-pane fade {if $cTab === 'topartikel'} active show{/if}">
                <div>
                    <form id="postzeitfilter" name="postzeitfilter" method="post" action="vergleichsliste.php">
                        {$jtl_token}
                        <input type="hidden" name="zeitfilter" value="1" />
                        <input type="hidden" name="tab" value="topartikel" />
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="nZeitFilter">{__('compareTimeFilter')}:</label>
                            <div class="col-sm-auto mb-3">
                                <select class="custom-select" id="nZeitFilter" name="nZeitFilter" onchange="document.postzeitfilter.submit();">
                                    <option value="1"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 1} selected{/if}>
                                        {__('last')} 24 {__('hours')}
                                    </option>
                                    <option value="7"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 7} selected{/if}>
                                        {__('last')} 7 {__('days')}
                                    </option>
                                    <option value="30"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 30} selected{/if}>
                                        {__('last')} 30 {__('days')}
                                    </option>
                                    <option value="365"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 365} selected{/if}>
                                        {__('lastYear')}
                                    </option>
                                </select>
                            </div>
                            <label class="col-sm-auto col-form-label" for="nAnzahl">{__('compareTopCount')}:</label>
                            <div class="col-sm-auto mb-3 min-w-sm">
                                <select class="custom-select" id="nAnzahl" name="nAnzahl" onchange="document.postzeitfilter.submit();">
                                    <option value="10"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 10} selected{/if}>
                                        10
                                    </option>
                                    <option value="20"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 20} selected{/if}>
                                        20
                                    </option>
                                    <option value="50"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 50} selected{/if}>
                                        50
                                    </option>
                                    <option value="100"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 100} selected{/if}>
                                        100
                                    </option>
                                    <option value="-1"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == -1} selected{/if}>
                                        {__('all')}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <hr class="mb-3">
                </div>
                <div>
                    {if isset($TopVergleiche) && $TopVergleiche|@count > 0}
                        <div class="settings table-responsive">
                            <table class="bottom table table-striped table-align-top">
                                <thead>
                                    <tr>
                                        <th class="text-left">{__('compareProduct')}</th>
                                        <th class="th-2 text-center">{__('compareCount')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach $TopVergleiche as $oVergleichslistePosTop}
                                    <tr>
                                        <td>
                                            <a href="{$shopURL}/index.php?a={$oVergleichslistePosTop->kArtikel}" target="_blank">{$oVergleichslistePosTop->cArtikelName}</a>
                                        </td>
                                        <td class="text-center">{$oVergleichslistePosTop->nAnzahl}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {/if}
                </div>
            </div>
            <div id="einstellungen" class="tab-pane fade {if $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='vergleichsliste.php' buttonCaption=__('saveWithIcon') title=__('settings') tab='einstellungen'}
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
