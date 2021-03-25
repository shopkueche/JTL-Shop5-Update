{include file='tpl_inc/header.tpl'}

{assign var=cFunAttrib value=$ART_ATTRIBUT_GRATISGESCHENKAB}

{include file='tpl_inc/seite_header.tpl' cTitel=__('ggHeader') cDokuURL=__('ggURL')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'aktivegeschenke'} active{/if}" data-toggle="tab" role="tab" href="#aktivegeschenke">
                        {__('ggActiveProducts')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'haeufigegeschenke'} active{/if}" data-toggle="tab" role="tab" href="#haeufigegeschenke">
                        {__('ggCommonBuyedProducts')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'letzten100geschenke'} active{/if}" data-toggle="tab" role="tab" href="#letzten100geschenke">
                        {__('ggLast100Products')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="aktivegeschenke" class="tab-pane fade {if $cTab === '' || $cTab === 'aktivegeschenke'} active show{/if}">
                {if isset($oAktiveGeschenk_arr) && $oAktiveGeschenk_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiAktiv cAnchor='aktivegeschenke'}
                    <div class="settings table-responsive">
                        <table class="table table-striped table-align-top">
                            <thead>
                            <tr>
                                <th class="text-left">{__('productName')}</th>
                                <th class="th-2 text-center">{__('ggOrderValueMin')}</th>
                                <th class="th-3 text-center">{__('ggDate')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $oAktiveGeschenk_arr as $oAktiveGeschenk}
                                <tr>
                                    <td>
                                        <a href="{$oAktiveGeschenk->cURLFull}" target="_blank">{$oAktiveGeschenk->cName}</a>
                                    </td>
                                    <td class="text-center">{getCurrencyConversionSmarty fPreisBrutto=$oAktiveGeschenk->FunktionsAttribute[$cFunAttrib]}</td>
                                    <td class="text-center">{$oAktiveGeschenk->dErstellt_de}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="haeufigegeschenke" class="tab-pane fade {if $cTab === 'haeufigegeschenke'} active show{/if}">
                {if isset($oHaeufigGeschenk_arr) && $oHaeufigGeschenk_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiHaeufig cAnchor='haeufigegeschenke'}
                    <div class="settings table-responsive">
                        <table class="table table-striped table-align-top">
                            <thead>
                            <tr>
                                <th class="text-left">{__('productName')}</th>
                                <th class="th-2 text-center">{__('ggOrderValueMin')}</th>
                                <th class="th-3 text-center">{__('ggCount')}</th>
                                <th class="th-3 text-center">{__('ggOrderValueAverage')}</th>
                                <th class="th-4 text-center">{__('gglastOrdered')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $oHaeufigGeschenk_arr as $oHaeufigGeschenk}
                                <tr>
                                    <td>
                                        <a href="{$oAktiveGeschenk->cURLFull}" target="_blank">{$oHaeufigGeschenk->artikel->cName}</a>
                                    </td>
                                    <td class="text-center">{getCurrencyConversionSmarty fPreisBrutto=$oHaeufigGeschenk->artikel->FunktionsAttribute[$cFunAttrib]}</td>
                                    <td class="text-center">{$oHaeufigGeschenk->artikel->nGGAnzahl} x</td>
                                    <td class="text-center">{getCurrencyConversionSmarty fPreisBrutto=$oHaeufigGeschenk->avgOrderValue}</td>
                                    <td class="text-center">{$oHaeufigGeschenk->lastOrdered}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="letzten100geschenke" class="tab-pane fade {if $cTab === 'letzten100geschenke'} active show{/if}">
                {if isset($oLetzten100Geschenk_arr) && $oLetzten100Geschenk_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiLetzte100 cAnchor='letzten100geschenke'}
                    <div class="settings table-responsive">
                        <table class="table table-striped table-align-top">
                            <thead>
                            <tr>
                                <th class="text-left">{__('productName')}</th>
                                <th class="th-2 text-center">{__('ggOrderValueMin')}</th>
                                <th class="th-4 text-center">{__('ggOrderValue')}</th>
                                <th class="th-4 text-center">{__('ggOrdered')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $oLetzten100Geschenk_arr as $oLetzten100Geschenk}
                                <tr>
                                    <td>
                                        <a href="{$oAktiveGeschenk->cURLFull}" target="_blank">{$oLetzten100Geschenk->artikel->cName}</a>
                                    </td>
                                    <td class="text-center">{getCurrencyConversionSmarty fPreisBrutto=$oLetzten100Geschenk->artikel->FunktionsAttribute[$cFunAttrib]}</td>
                                    <td class="text-center">{getCurrencyConversionSmarty fPreisBrutto=$oLetzten100Geschenk->orderValue}</td>
                                    <td class="text-center">{$oLetzten100Geschenk->orderCreated}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="einstellungen" class="tab-pane fade {if $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='gratisgeschenk.php' buttonCaption=__('saveWithIcon') title=__('settings') tab='einstellungen'}
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
