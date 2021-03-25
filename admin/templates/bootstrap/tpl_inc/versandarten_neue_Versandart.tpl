<script type="text/javascript">
    {assign var=addOne value=1}
    var i = {if isset($VersandartStaffeln) && $VersandartStaffeln|@count > 0}Number({$VersandartStaffeln|@count}) + 1{else}2{/if};
    function addInputRow() {ldelim}
        $('#price_range tbody').append('<tr><td><div class="input-group"><span class="input-group-addon"><label>{__('upTo')}</label></span><input type="text" name="bis[]"  id="bis' + i + '" class="form-control kilogram"><span class="input-group-addon"><label>{if isset($einheit)}{$einheit}{/if}</label></span></div></td><td class="text-center"><div class="input-group"><span class="input-group-addon"><label>{__('amount')}</label></span><input type="text" name="preis[]"  id="preis' + i + '" class="form-control price_large"></div></td></tr>');
        i += 1;
        {rdelim}

    function confirmAllCombi() {ldelim}
        return confirm('{__('shippingConfirm')}');
        {rdelim}

    {literal}
    function delInputRow() {
        i -= 1;
        $('#price_range tbody tr').last().remove();
    }

    function addShippingCombination() {
        var newCombi = '<div class=\'input-group align-baseline mt-2\'>'+$('#ulVK #liVKneu').html()+'</div>';
        newCombi = newCombi.replace(/selectX/gi,'select');
        if ($("select[name='Versandklassen']").length >= 1) {
            newCombi = newCombi.replace(/<option value="-1">/gi, '<option value="-1" disabled="disabled">');
        }

        $('#ulVK').append(newCombi);
    }

    function updateVK() {
        var val = '';
        $("select[name='Versandklassen']").each( function(index) {
            if ($(this).val()!= null) {
                val += ((val.length > 0)?' ':'') + $(this).val().toString().replace(/,/gi,'-');
            }
        });
        $("input[name='kVersandklasse']").val(val);
    }

    function checkCombination() {
        var remove = false;
        $("select[name='Versandklassen']").each(function (index) {
            if (index === 0) {
                if ($.inArray("-1", $(this).val()) != -1) {
                    if (!confirmAllCombi()) {
                        var valSelected = $(this).val();
                        valSelected.shift();
                        $(this).val(valSelected);
                        $('.select2').select2();
                        return false;
                    }
                    if ($("select[name='Versandklassen']").length >= 1) {
                        $(this).val("-1");
                        $('#addNewShippingClassCombi').prop('disabled', true);
                        remove = true;
                    }
                    $(this).val("-1");
                    $('#addNewShippingClassCombi').prop('disabled', true);
                    $('.select2').select2();
                } else {
                    $('#addNewShippingClassCombi').prop('disabled', false);
                }
            } else {
                if (remove) {
                    $(this).parent().parent().detach();
                }
            }
        });
    }
    {/literal}
</script>

{assign var=cTitel value=__('createShippingMethodTitle')}
{assign var=cBeschreibung value=__('createShippingMethodDesc')}

{if isset($Versandart->kVersandart) && $Versandart->kVersandart > 0}
    {assign var=cTitel value=__('modifyedShippingTypeTitle')|sprintf:$Versandart->cName}
    {assign var=cBeschreibung value=""}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=$cBeschreibung}
<div id="content">
    <form name="versandart_neu" method="post" action="versandarten.php">
        {$jtl_token}
        <input type="hidden" name="neueVersandart" value="1" />
        <input type="hidden" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" />
        <input type="hidden" name="kVersandart" value="{if isset($Versandart->kVersandart)}{$Versandart->kVersandart}{/if}" />
        <input type="hidden" name="cModulId" value="{$versandberechnung->cModulId}" />
        <div class="row">
            <div class="col-12 col-xl-6 settings">
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('general')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('shippingMethodName')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" type="text" id="cName" name="cName" value="{if isset($Versandart->cName)}{$Versandart->cName}{/if}" />
                            </div>
                        </div>
                        {foreach $availableLanguages as $language}
                            {assign var=cISO value=$language->getIso()}
                            {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cName_{$cISO}">{__('showedName')} ({$language->getLocalizedName()}):</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control" type="text" id="cName_{$cISO}" name="cName_{$cISO}" value="{if isset($oVersandartSpracheAssoc_arr[$cISO]->cName)}{$oVersandartSpracheAssoc_arr[$cISO]->cName}{/if}" />
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nSort">{__('sortnr')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" type="text" id="nSort" name="nSort" value="{if isset($Versandart->nSort)}{$Versandart->nSort}{/if}" />
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('versandartenSortDesc')}</div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cBild">{__('pictureURL')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" type="text" id="cBild" name="cBild" value="{if isset($Versandart->cBild)}{$Versandart->cBild}{/if}" />
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('pictureDesc')}</div>
                        </div>
                        {foreach $availableLanguages as $language}
                            {assign var=cISO value=$language->getIso()}
                            {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cHinweistextShop_{$cISO}">{__('shippingNoteShop')} ({$language->getLocalizedName()}):</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <textarea id="cHinweistextShop_{$cISO}" class="form-control combo" name="cHinweistextShop_{$cISO}">{if isset($oVersandartSpracheAssoc_arr[$cISO]->cHinweistextShop)}{$oVersandartSpracheAssoc_arr[$cISO]->cHinweistextShop}{/if}</textarea>
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                        {foreach $availableLanguages as $language}
                            {assign var=cISO value=$language->getIso()}
                            {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cHinweistext_{$cISO}">{__('shippingNoteEmail')} ({$language->getLocalizedName()}):</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <textarea id="cHinweistext_{$cISO}" class="form-control combo" name="cHinweistext_{$cISO}">{if isset($oVersandartSpracheAssoc_arr[$cISO]->cHinweistext)}{$oVersandartSpracheAssoc_arr[$cISO]->cHinweistext}{/if}</textarea>
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                        <div class="form-group form-row align-items-center mt-7">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nMinLiefertage">{__('minLiefertage')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" type="text" id="nMinLiefertage" name="nMinLiefertage" value="{if isset($Versandart->nMinLiefertage)}{$Versandart->nMinLiefertage}{/if}" />
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nMaxLiefertage">{__('maxLiefertage')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" type="text" id="nMaxLiefertage" name="nMaxLiefertage" value="{if isset($Versandart->nMaxLiefertage)}{$Versandart->nMaxLiefertage}{/if}" />
                            </div>
                        </div>
                        {foreach $availableLanguages as $language}
                            {assign var=cISO value=$language->getIso()}
                            {if isset($oVersandartSpracheAssoc_arr[$cISO])}
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cLieferdauer_{$cISO}">{__('shippingTime')} ({$language->getLocalizedName()}):</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control" type="text" id="cLieferdauer_{$cISO}" name="cLieferdauer_{$cISO}" value="{if isset($oVersandartSpracheAssoc_arr[$cISO]->cLieferdauer)}{$oVersandartSpracheAssoc_arr[$cISO]->cLieferdauer}{/if}" />
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                        <div class="form-group form-row align-items-center mt-7">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cAnzeigen">{__('showShippingMethod')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="cAnzeigen" id="cAnzeigen" class="custom-select combo">
                                    <option value="immer" {if isset($Versandart->cAnzeigen) && $Versandart->cAnzeigen === 'immer'}selected{/if}>{__('always')}</option>
                                    <option value="guenstigste" {if isset($Versandart->cAnzeigen) && $Versandart->cAnzeigen === 'guenstigste'}selected{/if}>{__('lowest')}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cIgnoreShippingProposal">{__('excludeShippingProposal')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="cIgnoreShippingProposal" id="cIgnoreShippingProposal" class="custom-select combo">
                                    <option value="N" {if isset($Versandart->cIgnoreShippingProposal) && $Versandart->cIgnoreShippingProposal === 'N'}selected{/if}>{__('no')}</option>
                                    <option value="Y" {if isset($Versandart->cIgnoreShippingProposal) && $Versandart->cIgnoreShippingProposal === 'Y'}selected{/if}>{__('yes')}</option>
                                </select>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('excludeShippingProposalDesc')}</div>
                        </div>

                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cNurAbhaengigeVersandart">{__('onlyForOwnShippingPrices')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="cNurAbhaengigeVersandart" id="cNurAbhaengigeVersandart" class="combo custom-select">
                                    <option value="N" {if isset($Versandart->cNurAbhaengigeVersandart) && $Versandart->cNurAbhaengigeVersandart === 'N'}selected{/if}>{__('no')}</option>
                                    <option value="Y" {if isset($Versandart->cNurAbhaengigeVersandart) && $Versandart->cNurAbhaengigeVersandart === 'Y'}selected{/if}>{__('yes')}</option>
                                </select>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('ownShippingPricesDesc')}</div>
                        </div>

                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="eSteuer">{__('taxshippingcosts')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="eSteuer" id="eSteuer" class="combo custom-select">
                                    <option value="brutto" {if isset($Versandart->eSteuer) && $Versandart->eSteuer === 'brutto'}selected{/if}>{__('gross')}</option>
                                    <option value="netto" {if isset($Versandart->eSteuer) && $Versandart->eSteuer === 'netto'}selected{/if}>{__('net')}</option>
                                </select>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('taxshippingcostsDesc')}</div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cSendConfirmationMail">{__('sendShippingNotification')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="cSendConfirmationMail" id="cSendConfirmationMail" class="combo custom-select">
                                    <option value="Y" {if isset($Versandart->cSendConfirmationMail) && $Versandart->cSendConfirmationMail === 'Y'}selected{/if}>{__('yes')}</option>
                                    <option value="N" {if isset($Versandart->cSendConfirmationMail) && $Versandart->cSendConfirmationMail === 'N'}selected{/if}>{__('no')}</option>
                                </select>
                            </div>
                            {*<span class="input-group-addon">{getHelpDesc cDesc=''}</span>*}
                        </div>
                        <div class="form-group form-row align-items-center mt-7">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppe">{__('customerclass')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="kKundengruppe[]"
                                        id="kKundengruppe"
                                        multiple="multiple"
                                        class="selectpicker custom-select"
                                        data-selected-text-format="count > 2"
                                        data-size="7"
                                        data-actions-box="true">
                                    <option value="-1" {if $gesetzteKundengruppen.alle}selected{/if}>{__('all')}</option>
                                    <option data-divider="true"></option>
                                    {foreach $customerGroups as $customerGroup}
                                        {assign var=classID value=$customerGroup->getID()}
                                        <option value="{$classID}" {if isset($gesetzteKundengruppen.$classID) && $gesetzteKundengruppen.$classID}selected{/if}>{$customerGroup->getName()}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('customerclassDesc')}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="card">
                    <div class="card-body">
                    {if $versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl' || $versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl' || $versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'}
                        <div class="subheading1">{__('priceScale')}</div>
                        <hr class="mb-3">
                        <ul class="jtl-list-group">
                            <li class="input-group">
                                <table id="price_range" class="table">
                                    <thead></thead>
                                    <tbody>
                                    {if isset($VersandartStaffeln) && $VersandartStaffeln|@count > 0}
                                        {foreach $VersandartStaffeln as $oPreisstaffel}
                                            {if $oPreisstaffel->fBis != 999999999}
                                                <tr>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><label>{__('upTo')}</label></span>
                                                            <input type="text" id="bis{$oPreisstaffel@index}" name="bis[]" value="{if isset($VersandartStaffeln[$oPreisstaffel@index]->fBis)}{$VersandartStaffeln[$oPreisstaffel@index]->fBis}{/if}" class="form-control kilogram" />
                                                            <span class="input-group-addon"><label>{$einheit}</label></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><label>{__('amount')}:</label></span>
                                                            <input type="text" id="preis{$oPreisstaffel@index}" name="preis[]" value="{if isset($VersandartStaffeln[$oPreisstaffel@index]->fPreis)}{$VersandartStaffeln[$oPreisstaffel@index]->fPreis}{/if}" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxpreisstaffel{$oPreisstaffel@index}', this)" /> <span id="ajaxpreisstaffel{$oPreisstaffel@index}"></span>*}
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/if}
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><label>{__('upTo')}</label></span>
                                                    <input type="text" id="bis1" name="bis[]" value="" class="form-control kilogram" />
                                                    <span class="input-group-addon"><label>{$einheit}</label></span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><label>{__('amount')}:</label></span>
                                                    <input type="text" id="preis1" name="preis[]" value="" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxpreis1', this)" /> <span id="ajaxpreis1"></span>*}
                                                </div>
                                            </td>
                                        </tr>
                                    {/if}
                                    </tbody>
                                </table>
                            </li>
                        </ul>
                        <div class="row">
                            <div class="ml-auto col-sm-6 mb-2">
                                <button name="delRow" type="button" value="{__('delPriceScale')}" onclick="delInputRow();" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-trash-alt"></i> {__('delPriceScale')}
                                </button>
                            </div>
                            <div class="col-sm-6">
                                <button name="addRow" type="button" value="{__('addPriceScale')}" onclick="addInputRow();" class="btn btn-primary btn-block">
                                    <i class="fas fa-share"></i> {__('addPriceScale')}
                                </button>
                            </div>
                        </div>
                        <hr class="mb-3">
                    {elseif $versandberechnung->cModulId === 'vm_versandkosten_pauschale_jtl'}
                        <div class="subheading1">{__('shippingPrice')}</div>
                        <hr class="mb-3">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right">
                                {__('amount')}:
                            </label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="text" id="fPreisNetto" name="fPreis" value="{if isset($Versandart->fPreis)}{$Versandart->fPreis}{/if}" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxfPreisNetto', this)" /> <span id="ajaxfPreisNetto"></span>*}
                            </div>
                        </div>
                    {/if}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right">
                                {__('freeShipping')}:
                            </label>
                            <div class="col-sm-4 pl-sm-3 order-last order-sm-2">
                                <select id="versandkostenfreiAktiv" name="versandkostenfreiAktiv" class="custom-select">
                                    <option value="0">{__('no')}</option>
                                    <option value="1" {if isset($Versandart->fVersandkostenfreiAbX) && $Versandart->fVersandkostenfreiAbX > 0}selected{/if}>{__('yes')}</option>
                                </select>
                            </div>
                            <div class="col-sm pr-sm-5 order-last order-sm-2">
                                <input type="text" id="fVersandkostenfreiAbX" name="fVersandkostenfreiAbX" class="form-control price_large" value="{if isset($Versandart->fVersandkostenfreiAbX)}{$Versandart->fVersandkostenfreiAbX}{/if}">{* onKeyUp="setzePreisAjax(false, 'ajaxversandkostenfrei', this)" /> <span id="ajaxversandkostenfrei"></span>*}
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right">
                                {__('maxCosts')}:
                            </label>
                            <div class="col-sm-4 pl-sm-3 order-last order-sm-2">
                                <select id="versanddeckelungAktiv" name="versanddeckelungAktiv" class="combo custom-select">
                                    <option value="0">{__('no')}</option>
                                    <option value="1" {if isset($Versandart->fDeckelung) && $Versandart->fDeckelung > 0}selected{/if}>{__('yes')}</option>
                                </select>
                            </div>
                            <div class="col-sm pr-sm-5 order-last order-sm-2">
                                <input type="text" id="fDeckelung" name="fDeckelung" value="{if isset($Versandart->fDeckelung)}{$Versandart->fDeckelung}{/if}" class="form-control price_large">{* onKeyUp="setzePreisAjax(false, 'ajaxdeckelung', this)" /> <span id="ajaxdeckelung"></span>*}
                            </div>
                        </div>
                        <div class="mt-7">
                            <div class="subheading1">{__('validOnShippingClasses')} {getHelpDesc cDesc=__('shippingclassDesc')}</div>
                            <hr class="mb-3">
                            <input name="kVersandklasse" type="hidden" value="{if !empty($Versandart->cVersandklassen)}{$Versandart->cVersandklassen}{else}-1{/if}">
                            <div id="ulVK" class="jtl-list-group">
                                <div id='liVKneu' class="input-group al" style="display:none;">
                                    <div class="col-sm">
                                        <selectX class="selectX2 custom-select" name="Versandklassen"
                                                 onchange="checkCombination();updateVK();"
                                                 multiple>
                                            <option value="-1">{__('allCombinations')}</option>
                                            {foreach $versandKlassen as $vk}
                                                <option value="{$vk->kVersandklasse}">{$vk->cName}</option>
                                            {/foreach}
                                        </selectX>
                                    </div>
                                    <div>
                                        <button class="btn btn-link pl-0" type="button"
                                                onclick="$(this).parent().parent().detach(); updateVK();">
                                            <span class="far fa-trash-alt"></span>
                                        </button>
                                    </div>
                                </div>
                                {if !empty($Versandart->cVersandklassen)}
                                    {$aVK = ' '|explode:$Versandart->cVersandklassen}
                                    {foreach $aVK as $VK}
                                        <div class="input-group align-baseline mt-2">
                                            <div class="col-sm">
                                                <select class="select2 custom-select" name="Versandklassen"
                                                        onchange="checkCombination();updateVK();" multiple="multiple">
                                                    <option value="-1"{if $VK@iteration > 1} disabled="disabled"{/if}{if $VK === '-1'} selected{/if}>{__('allCombinations')}</option>
                                                    {if $VK === '-1'}
                                                        {foreach $versandKlassen as $vclass}
                                                            <option value="{$vclass->kVersandklasse}">{$vclass->cName}</option>
                                                        {/foreach}
                                                    {else}
                                                        {$vkID = '-'|explode:$VK}
                                                        {foreach $versandKlassen as $vclass}
                                                        <option value="{$vclass->kVersandklasse}"{if $vclass->kVersandklasse|in_array:$vkID} selected{/if}>{$vclass->cName}</option>
                                                    {/foreach}
                                                    {/if}
                                                </select>
                                            </div>
                                            {if $VK@iteration != 1}
                                                <div>
                                                    <button class="btn btn-link pl-0" type="button"
                                                            onclick="$(this).parent().parent().detach(); updateVK();">
                                                        <span class="far fa-trash-alt"></span>
                                                    </button>
                                                </div>
                                            {/if}
                                        </div>
                                    {/foreach}
                                {else}
                                    <div class="input-group">
                                        <select class="select2 custom-select" name="Versandklassen"
                                                onchange="checkCombination();updateVK();" multiple="multiple">
                                            <option value="-1">{__('allCombinations')}</option>
                                            {foreach $versandKlassen as $vclass}
                                                <option value="{$vclass->kVersandklasse}">{$vclass->cName}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                {/if}
                            </div>
                            <div class="row mt-4">
                                {if !empty($missingShippingClassCombis)}
                                    <div class="ml-auto col-sm-6 col-lg-auto">
                                        <button class="btn btn-warning btn-block" type="button" data-toggle="collapse" data-target="#collapseShippingClasses" aria-expanded="false" aria-controls="collapseShippingClasses">
                                            {__('showMissingCombinations')}
                                        </button>
                                    </div>
                                {/if}
                                <div class="{if empty($missingShippingClassCombis)}ml-auto{/if} col-sm-6 col-lg-auto mb-2">
                                    <button id="addNewShippingClassCombi" class="btn btn-primary btn-block" type="button"
                                            onclick="addShippingCombination();$('.select2').select2();">
                                        <span class="far fa-plus"></span> {__('addShippingClass')}
                                    </button>
                                </div>
                            </div>
                            {if !empty($missingShippingClassCombis)}
                                <div class="collapse row" id="collapseShippingClasses">
                                    {if $missingShippingClassCombis === -1}
                                        <div class="col-xs-12">
                                            {__('coverageShippingClassCombination')}
                                            {__('noShipClassCombiValidation')|replace:'%s':$smarty.const.SHIPPING_CLASS_MAX_VALIDATION_COUNT}
                                        </div>
                                    {else}
                                        {foreach $missingShippingClassCombis as $mscc}
                                            <div class="col-auto">
                                                <span class="badge badge-info">{$mscc}</span>
                                            </div>
                                        {/foreach}
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('acceptedPaymentMethods')} {getHelpDesc cDesc=__('acceptedPaymentMethodsDesc')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        {foreach $zahlungsarten as $zahlungsart}
                            {assign var=kZahlungsart value=$zahlungsart->kZahlungsart}
                            <div class="form-group form-row align-items-center mb-5 mb-md-3">
                                <div class="col-12 col-md mb-1 mb-md-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                               id="kZahlungsart{$zahlungsart@index}"
                                               name="kZahlungsart[]"
                                               class="custom-control-input"
                                               value="{$kZahlungsart}"
                                                {if isset($VersandartZahlungsarten[$kZahlungsart]->checked)}{$VersandartZahlungsarten[$kZahlungsart]->checked}{/if} />
                                        <label class="custom-control-label" for="kZahlungsart{$zahlungsart@index}">
                                            {$zahlungsart->cName}{if isset($zahlungsart->cAnbieter) && $zahlungsart->cAnbieter|strlen > 0} ({$zahlungsart->cAnbieter}){/if}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-auto ml-md-3 text-md-right">{__('discount')}:</div>
                                <div class="col ml-md-3">
                                    <input type="text" id="Netto_{$kZahlungsart}" name="fAufpreis_{$kZahlungsart}" value="{if isset($VersandartZahlungsarten[$kZahlungsart]->fAufpreis)}{$VersandartZahlungsarten[$kZahlungsart]->fAufpreis}{/if}" class="form-control price_large"{* onKeyUp="setzePreisAjax(false, 'ZahlungsartAufpreis_{$zahlungsart->kZahlungsart}', this)"*} />
                                </div>
                                <div class="col-auto ml-md-3">
                                    <select name="cAufpreisTyp_{$kZahlungsart}" id="cAufpreisTyp_{$kZahlungsart}" class="custom-select">
                                        <option value="festpreis"{if isset($VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp) && $VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp === 'festpreis'} selected{/if}>
                                            {__('amount')}
                                        </option>
                                        <option value="prozent"{if isset($VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp) && $VersandartZahlungsarten[$kZahlungsart]->cAufpreisTyp === 'prozent'} selected{/if}>
                                            %
                                        </option>
                                    </select>
                                    <span id="ZahlungsartAufpreis_{$zahlungsart->kZahlungsart}" class="ZahlungsartAufpreis"></span>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('shipToCountries')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="accordion" id="shippingTo">
                    {foreach $continents as $continentKey => $continent}
                    <div class="accordion-row">
                        <div class="accordion-header" id="continent-heading-{$continentKey}">
                            <div class="row align-items-center">
                                <div class="col mr-auto accordion-title cursor-pointer"
                                     data-toggle="collapse"
                                     data-target="#collapse-continent-{$continentKey}"
                                     aria-expanded="false"
                                     aria-controls="collapse-continent-{$continentKey}">
                                    {$continent->name}
                                </div>
                                <div class="col-auto ml-auto">
                                    <button class="btn btn-link text-decoration-none btn-sm text-muted font-size-base dropdown-toggle"
                                            type="button"
                                            data-toggle="collapse"
                                            data-target="#collapse-continent-{$continentKey}"
                                            aria-expanded="false"
                                            aria-controls="collapse-continent-{$continentKey}">
                                        {$continent->countriesCount} {__('countries')}
                                        <i class="far fa-chevron-down rotate-180 ml-2"></i>
                                    </button>
                                </div>
                                <div class="w-100 d-md-none"></div>
                                <div class="col-auto cursor-pointer"
                                     data-toggle="collapse"
                                     data-target="#collapse-continent-{$continentKey}"
                                     aria-expanded="false"
                                     aria-controls="collapse-continent-{$continentKey}">
                                    <span data-select-all-count="collapse-continent-{$continentKey}">{$continent->countriesSelectedCount}</span> {__('countriesSelected')}
                                </div>
                                <div class="col-auto ml-auto">
                                    <button class="btn btn-link btn-sm font-size-base pr-2"
                                            type="button"
                                            data-select-all="collapse-continent-{$continentKey}"
                                            aria-controls="countryCollapse-<?php echo $i; ?>">
                                        <span class="fal fa-check-square mr-2"></span> {__('all')}
                                    </button>
                                    <button class="btn btn-link btn-sm font-size-base pl-2"
                                            type="button"
                                            data-deselect-all="collapse-continent-{$continentKey}"
                                            aria-controls="countryCollapse-<?php echo $i; ?>">
                                        <span class="fal fa-square mr-2"></span> {__('none')}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-body">
                            <div id="collapse-continent-{$continentKey}" class="collapse" aria-labelledby="continent-heading-{$continentKey}" data-parent="#shippingTo">
                                <div class="row">
                                    {foreach $continent->countries as $country}
                                    <div class="col-sm-6 col-md-4 col-xl-3">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input"
                                                   type="checkbox" name="land[]"
                                                   data-id="country_{$country->getISO()}"
                                                   value="{$country->getISO()}"
                                                   id="country_{$country->getISO()}"
                                                    {if isset($gewaehlteLaender) && is_array($gewaehlteLaender) && in_array($country->getISO(),$gewaehlteLaender)} checked="checked"{/if} />
                                            <label class="custom-control-label" for="country_{$country->getISO()}">{$country->getName()}</label>
                                        </div>
                                    </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                    {/foreach}
                </div>
            </div>
        </div>
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-lg-auto">
                    <a href="versandarten.php" title="{__('cancel')}" class="btn btn-outline-primary btn-block">
                        {__('cancelWithIcon')}
                    </a>
                </div>
                <div class="col-sm-6 col-lg-auto">
                    <button type="submit"
                            value="{if !isset($Versandart->kVersandart) || !$Versandart->kVersandart}{__('createShippingType')}{else}{__('modifyedShippingType')}{/if}"
                            class="btn btn-primary btn-block">
                        {if !isset($Versandart->kVersandart) || !$Versandart->kVersandart}
                            <i class="fa fa-share"></i> {__('createShippingType')}
                        {else}
                            {__('saveWithIcon')}
                        {/if}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
{literal}
    <script type="text/javascript">
        $('input[name="land[]"]').on('change', function () {
            $('input[data-id="' + $(this).data('id') + '"]').prop('checked', $(this).prop('checked'));
        });
    </script>
{/literal}
