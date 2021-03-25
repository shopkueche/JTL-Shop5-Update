<link type="text/css" rel="stylesheet" href="{$templateBaseURL}js/js_calender/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css" media="screen" />
<script type="text/javascript" src="{$templateBaseURL}js/js_calender/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js"></script>
<script type="text/javascript">
var fields = 0;

function neu() {ldelim}
    if (fields !== 10) {ldelim}
        document.getElementById('ArtNr').innerHTML += '<input name="cArtNr[]" type="text" class="field" />';
        fields += 1;
    {rdelim} else {ldelim}
        document.getElementById('ArtNr').innerHTML += '';
        document.form.add.disabled=true;
    {rdelim}
{rdelim}

function checkNewsletterSend() {ldelim}
    var bCheck = confirm("{__('newsletterSendAuthentication')}");
    if(bCheck) {ldelim}
        var input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'speichern_und_senden';
        input1.value = '1';
        document.getElementById('formnewslettervorlage').appendChild(input1);
        document.formnewslettervorlage.submit();
    {rdelim}
{rdelim}
</script>

<div id="page">
   {include file='tpl_inc/seite_header.tpl' cTitel=__('newsletterdraft') cBeschreibung=__('newsletterdraftdesc')}
    <div id="content">
        <form name="formnewslettervorlage" id="formnewslettervorlage" method="post" action="newsletter.php">
            {$jtl_token}
            <input name="newslettervorlagen" type="hidden" value="1">
            <input name="tab" type="hidden" value="newslettervorlagen">

            {if isset($oNewsletterVorlage->kNewsletterVorlage) && $oNewsletterVorlage->kNewsletterVorlage}
                <input name="kNewsletterVorlage" type="hidden" value="{$oNewsletterVorlage->kNewsletterVorlage}">
            {/if}
            <div class="card settings">
                <div class="card-header">
                    <div class="subheading1">{__('newsletterdraftcreate')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center {if isset($cPlausiValue_arr.cName)}error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('newsletterdraftname')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input id="cName"
                                   name="cName"
                                   type="text"
                                   class="form-control"
                                   value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($oNewsletterVorlage->cName)}{$oNewsletterVorlage->cName}{/if}"
                                   required>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center {if isset($cPlausiValue_arr.cBetreff)}error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cBetreff">{__('subject')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input id="cBetreff"
                                   name="cBetreff"
                                   type="text"
                                   class="form-control"
                                   value="{if isset($cPostVar_arr.cBetreff)}{$cPostVar_arr.cBetreff}{elseif isset($oNewsletterVorlage->cBetreff)}{$oNewsletterVorlage->cBetreff}{/if}"
                                   required>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center {if isset($cPlausiValue_arr.kKundengruppe_arr)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppe">{__('newslettercustomergrp')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="kKundengruppe"
                                    name="kKundengruppe[]"
                                    multiple="multiple"
                                    class="selectpicker custom-select {if !isset($cPlausiValue_arr.kKundengruppe_arr)}combo{/if}"
                                    data-selected-text-format="count > 2"
                                    data-size="7"
                                    data-actions-box="true"
                                    required>
                                <option value="0"
                                        {if isset($kKundengruppe_arr)}
                                            {foreach $kKundengruppe_arr as $kKundengruppe}
                                                {if $kKundengruppe == '0'}selected{/if}
                                            {/foreach}
                                        {elseif isset($cPostVar_arr.kKundengruppe)}
                                            {foreach $cPostVar_arr.kKundengruppe as $kKundengruppe}
                                                {if $kKundengruppe == '0'}selected{/if}
                                            {/foreach}
                                        {/if}
                                        >{__('newsletterNoAccount')}</option>
                                {foreach $customerGroups as $customerGroup}
                                    <option value="{$customerGroup->getID()}"
                                            {if isset($kKundengruppe_arr)}
                                                {foreach $kKundengruppe_arr as $kKundengruppe}
                                                    {if $customerGroup->getID() === (int)$kKundengruppe}selected{/if}
                                                {/foreach}
                                            {elseif isset($cPostVar_arr.kKundengruppe)}
                                                {foreach $cPostVar_arr.kKundengruppe as $kKundengruppe}
                                                    {if $customerGroup->getID() === (int)$kKundengruppe}selected{/if}
                                                {/foreach}
                                            {/if}
                                            >{$customerGroup->getName()}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cArt">{__('newsletterdraftcharacter')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="cArt" name="cArt" class="custom-select combo">
                                <option {if isset($oNewsletterVorlage->cArt) && $oNewsletterVorlage->cArt === 'text/html'}selected{/if}>{__('textHtml')}</option>
                                <option {if isset($oNewsletterVorlage->cArt) && $oNewsletterVorlage->cArt === 'text'}selected{/if}>{__('text')}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cArt">
                            {__('newsletterdraftdate')} ({__('newsletterdraftformat')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <div class="row no-gutters">
                                <span class="col-sm-auto cols-12">
                                    <select name="dTag" class="custom-select combo" style="width:100%;">
                                        {section name=dTag start=1 loop=32 step=1}
                                            {if $smarty.section.dTag.index < 10}
                                                <option value="0{$smarty.section.dTag.index}"{if isset($oNewsletterVorlage->oZeit->cZeit_arr) && $oNewsletterVorlage->oZeit->cZeit_arr|@count > 0}{if $oNewsletterVorlage->oZeit->cZeit_arr[0] == $smarty.section.dTag.index} selected{/if}{else}{if $smarty.now|date_format:'%d' == $smarty.section.dTag.index} selected{/if}{/if}>0{$smarty.section.dTag.index}</option>
                                            {else}
                                                <option value="{$smarty.section.dTag.index}"{if isset($oNewsletterVorlage->oZeit->cZeit_arr) && $oNewsletterVorlage->oZeit->cZeit_arr|@count > 0}{if $oNewsletterVorlage->oZeit->cZeit_arr[0] == $smarty.section.dTag.index} selected{/if}{else}{if $smarty.now|date_format:'%d' == $smarty.section.dTag.index} selected{/if}{/if}>{$smarty.section.dTag.index}</option>
                                            {/if}
                                        {/section}
                                    </select>
                                </span>
                                <span class="col-sm-auto cols-12">
                                    <select name="dMonat" class="custom-select combo" style="width:100%;">
                                        {section name=dMonat start=1 loop=13 step=1}
                                            {if $smarty.section.dMonat.index < 10}
                                                <option value="0{$smarty.section.dMonat.index}"{if isset($oNewsletterVorlage->oZeit->cZeit_arr) && $oNewsletterVorlage->oZeit->cZeit_arr|@count > 0}{if $oNewsletterVorlage->oZeit->cZeit_arr[1] == $smarty.section.dMonat.index} selected{/if}{else}{if $smarty.now|date_format:'%m' == $smarty.section.dMonat.index} selected{/if}{/if}>0{$smarty.section.dMonat.index}</option>
                                            {else}
                                                <option value="{$smarty.section.dMonat.index}"{if isset($oNewsletterVorlage->oZeit->cZeit_arr) && $oNewsletterVorlage->oZeit->cZeit_arr|@count > 0}{if $oNewsletterVorlage->oZeit->cZeit_arr[1] == $smarty.section.dMonat.index} selected{/if}{else}{if $smarty.now|date_format:'%m' == $smarty.section.dMonat.index} selected{/if}{/if}>{$smarty.section.dMonat.index}</option>
                                            {/if}
                                        {/section}
                                    </select>
                                </span>
                                <span class="col-sm-auto cols-12">
                                    <select name="dJahr" class="custom-select combo" style="width:100%;">
                                        {$Y = $smarty.now|date_format:'%Y'}
                                        {section name=dJahr start=$Y loop=($Y+2) step=1}
                                            <option value="{$smarty.section.dJahr.index}"{if isset($oNewsletterVorlage->oZeit->cZeit_arr) && $oNewsletterVorlage->oZeit->cZeit_arr|@count > 0}{if $oNewsletterVorlage->oZeit->cZeit_arr[2] == $smarty.section.dJahr.index} selected{/if}{else}{if $smarty.now|date_format:'%Y' == $smarty.section.dJahr.index} selected{/if}{/if}>{$smarty.section.dJahr.index}</option>
                                        {/section}
                                    </select>
                                </span>
                                <span class="col-sm-auto cols-12">
                                    <select name="dStunde" class="custom-select combo" style="width:100%;">
                                        {section name=dStunde start=0 loop=24 step=1}
                                            {if $smarty.section.dStunde.index < 10}
                                                <option value="0{$smarty.section.dStunde.index}"{if isset($oNewsletterVorlage->oZeit->cZeit_arr) && $oNewsletterVorlage->oZeit->cZeit_arr|@count > 0}{if $oNewsletterVorlage->oZeit->cZeit_arr[3] == $smarty.section.dStunde.index} selected{/if}{else}{if $smarty.now|date_format:'%H' == $smarty.section.dStunde.index} selected{/if}{/if}>0{$smarty.section.dStunde.index}</option>
                                            {else}
                                                <option value="{$smarty.section.dStunde.index}"{if isset($oNewsletterVorlage->oZeit->cZeit_arr) && $oNewsletterVorlage->oZeit->cZeit_arr|@count > 0}{if $oNewsletterVorlage->oZeit->cZeit_arr[3] == $smarty.section.dStunde.index} selected{/if}{else}{if $smarty.now|date_format:'%H' == $smarty.section.dStunde.index} selected{/if}{/if}>{$smarty.section.dStunde.index}</option>
                                            {/if}
                                        {/section}
                                    </select>
                                </span>
                                <span class="col-sm-auto cols-12">
                                    <select name="dMinute" class="custom-select combo" style="width:100%;">
                                        {section name=dMinute start=0 loop=60 step=1}
                                            {if $smarty.section.dMinute.index < 10}
                                                <option value="0{$smarty.section.dMinute.index}"{if isset($oNewsletterVorlage->oZeit->cZeit_arr) && $oNewsletterVorlage->oZeit->cZeit_arr|@count > 0}{if $oNewsletterVorlage->oZeit->cZeit_arr[4] == $smarty.section.dMinute.index} selected{/if}{else}{if $smarty.now|date_format:'%M' == $smarty.section.dMinute.index} selected{/if}{/if}>0{$smarty.section.dMinute.index}</option>
                                            {else}
                                                <option value="{$smarty.section.dMinute.index}"{if isset($oNewsletterVorlage->oZeit->cZeit_arr) && $oNewsletterVorlage->oZeit->cZeit_arr|@count > 0}{if $oNewsletterVorlage->oZeit->cZeit_arr[4] == $smarty.section.dMinute.index} selected{/if}{else}{if $smarty.now|date_format:'%M' == $smarty.section.dMinute.index} selected{/if}{/if}>{$smarty.section.dMinute.index}</option>
                                            {/if}
                                        {/section}
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kKampagne">{__('campaign')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="custom-select " id="kKampagne" name="kKampagne">
                                <option value="0"></option>
                                {foreach $oKampagne_arr as $oKampagne}
                                    <option value="{$oKampagne->kKampagne}"{if isset($oNewsletterVorlage->kKampagne) && $oKampagne->kKampagne == $oNewsletterVorlage->kKampagne || (isset($cPostVar_arr.kKampagne) && isset($oKampagne->kKampagne) && $cPostVar_arr.kKampagne == $oKampagne->kKampagne)} selected{/if}>{$oKampagne->cName}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    {include file='tpl_inc/searchpicker_modal.tpl'
                        searchPickerName='articlePicker'
                        modalTitle="{__('titleChooseProducts')}"
                        searchInputLabel="{__('labelSearchProduct')}"
                    }
                    <script>
                        $(function () {
                            articlePicker = new SearchPicker({
                                searchPickerName:  'articlePicker',
                                getDataIoFuncName: 'getProducts',
                                keyName:           'cArtNr',
                                renderItemCb:      function (item) { return item.cName; },
                                onApply:           onApplySelectedArticles,
                                selectedKeysInit:  $('#cArtikel').val().split(';').filter(Boolean)
                            });
                            onApplySelectedArticles(articlePicker.getSelection());
                        });
                        function onApplySelectedArticles(selected)
                        {
                            $('#articleSelectionInfo')
                                .val(selected.length > 0 ? selected.length + ' {__('product')}' : '');
                            $('#cArtikel')
                                .val(selected.length > 0 ? selected.join(';') + ';' : '');
                        }
                    </script>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="articleSelectionInfo">{__('newsletterartnr')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" readonly="readonly" id="articleSelectionInfo">
                            <input type="hidden" id="cArtikel" name="cArtikel"
                                   value="{if isset($cPostVar_arr.cArtikel) && $cPostVar_arr.cArtikel|strlen > 0}{$cPostVar_arr.cArtikel}{elseif isset($oNewsletterVorlage->cArtikel)}{$oNewsletterVorlage->cArtikel}{/if}">
                        </div>
                        <div class="col-sm-auto ml-sm-n4 order-2 order-sm-3">
                            {include file='snippets/searchpicker_button.tpl' target='#articlePicker-modal'}
                        </div>
                    </div>
                    {include file='tpl_inc/searchpicker_modal.tpl'
                        searchPickerName='manufacturerPicker'
                        modalTitle="{__('titleChooseManufacturer')}"
                        searchInputLabel="{__('labelSearchManufacturer')}"
                    }
                    <script>
                        $(function () {
                            manufacturerPicker = new SearchPicker({
                                searchPickerName:  'manufacturerPicker',
                                getDataIoFuncName: 'getManufacturers',
                                keyName:           'kHersteller',
                                renderItemCb:      function (item) { return item.cName; },
                                onApply:           onApplySelectedManufacturers,
                                selectedKeysInit:  $('#cHersteller').val().split(';').filter(Boolean)
                            });
                            onApplySelectedManufacturers(manufacturerPicker.getSelection());
                        });
                        function onApplySelectedManufacturers(selected)
                        {
                            $('#manufacturerSelectionInfo')
                                .val(selected.length > 0 ? selected.length + ' {__('manufacturer')}' : '');
                            $('#cHersteller')
                                .val(selected.length > 0 ? selected.join(';') + ';' : '');
                        }
                    </script>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="manufacturerSelectionInfo">{__('manufacturer')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" readonly="readonly" id="manufacturerSelectionInfo">
                            <input type="hidden" id="cHersteller" name="cHersteller"
                                   value="{if isset($cPostVar_arr.cHersteller) && $cPostVar_arr.cHersteller|strlen > 0}{$cPostVar_arr.cHersteller}{elseif isset($oNewsletterVorlage->cHersteller)}{$oNewsletterVorlage->cHersteller}{/if}">
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {include file='snippets/searchpicker_button.tpl' target='#manufacturerPicker-modal'}
                        </div>
                    </div>
                    {include file='tpl_inc/searchpicker_modal.tpl'
                        searchPickerName='categoryPicker'
                        modalTitle="{__('titleChooseCategory')}"
                        searchInputLabel="{__('labelSearchCategory')}"
                    }
                    <script>
                        $(function () {
                            categoryPicker = new SearchPicker({
                                searchPickerName:  'categoryPicker',
                                getDataIoFuncName: 'getCategories',
                                keyName:           'kKategorie',
                                renderItemCb:      function (item) { return item.cName; },
                                onApply:           onApplySelectedCategories,
                                selectedKeysInit:  $('#cKategorie').val().split(';').filter(Boolean)
                            });
                            onApplySelectedCategories(categoryPicker.getSelection());
                        });
                        function onApplySelectedCategories(selected)
                        {
                            $('#categorySelectionInfo')
                                .val(selected.length > 0 ? selected.length + ' {__('category')}' : '');
                            $('#cKategorie')
                                .val(selected.length > 0 ? selected.join(';') + ';' : '');
                        }
                    </script>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="categorySelectionInfo">{__('categories')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" readonly="readonly" id="categorySelectionInfo">
                            <input type="hidden" id="cKategorie" name="cKategorie"
                                   value="{if isset($cPostVar_arr.cKategorie) && $cPostVar_arr.cKategorie|strlen > 0}{$cPostVar_arr.cKategorie}{elseif isset($oNewsletterVorlage->cKategorie)}{$oNewsletterVorlage->cKategorie}{/if}">
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {include file='snippets/searchpicker_button.tpl' target='#categoryPicker-modal'}
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center {if isset($cPlausiValue_arr.cHtml)} error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cHtml">{__('newsletterHtml')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea class="codemirror smarty form-control" id="cHtml" name="cHtml">{if isset($cPostVar_arr.cHtml)}{$cPostVar_arr.cHtml}{elseif isset($oNewsletterVorlage->cInhaltHTML)}{$oNewsletterVorlage->cInhaltHTML}{/if}</textarea>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center {if isset($cPlausiValue_arr.cText)} error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cText">{__('newsletterText')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea class="codemirror smarty form-control" id="cText" name="cText">{if isset($cPostVar_arr.cText)}{$cPostVar_arr.cText}{elseif isset($oNewsletterVorlage->cInhaltText)}{$oNewsletterVorlage->cInhaltText}{/if}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a class="btn btn-outline-primary btn-block" href="newsletter.php?tab=newslettervorlagen&token={$smarty.session.jtl_token}">
                                {__('cancelWithIcon')}
                            </a>
                        </div>
                        {if $cOption !== 'editieren'}
                            <div class="col-sm-6 col-xl-auto">
                                <button class="btn btn-outline-primary btn-block" name="speichern_und_senden" type="button" value="{__('newsletterdraftsaveandsend')}" onclick="checkNewsletterSend();">{__('newsletterdraftsaveandsend')}</button>
                            </div>
                        {/if}
                        <div class="col-sm-6 col-xl-auto">
                            <button class="btn btn-outline-primary btn-block" name="speichern_und_testen" type="submit" value="{__('newsletterdraftsaveandtest')}">{__('newsletterdraftsaveandtest')}</button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button class="btn btn-primary btn-block" name="speichern" type="submit" value="{__('save')}">{__('saveWithIcon')}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {if !empty($oNewsletterVorlage->kNewsletterVorlage)}
            {getRevisions type='newsletter' key=$oNewsletterVorlage->kNewsletterVorlage show=['cInhaltHTML', 'cInhaltText'] secondary=false data=$oNewsletterVorlage}
        {/if}
    </div>
</div>
