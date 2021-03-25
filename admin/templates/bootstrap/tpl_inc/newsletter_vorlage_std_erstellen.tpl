<link type="text/css" rel="stylesheet" href="{$templateBaseURL}js/js_calender/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css" media="screen" />
<script type="text/javascript" src="{$templateBaseURL}js/js_calender/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js"></script>
<script type="text/javascript">
    var fields = 0;

    function neu() {ldelim}
        if (fields !== 10) {ldelim}
            document.getElementById('ArtNr').innerHTML += "<input name='cArtNr[]' type='text' class='field'>";
            fields += 1;
        {rdelim} else {ldelim}
            document.getElementById('ArtNr').innerHTML += "";
            document.form.add.disabled = true;
        {rdelim}
    {rdelim}

    function checkNewsletterSend() {ldelim}
        var bCheck = confirm("{__('newsletterSendAuthentication')}");

        if (bCheck) {ldelim}
            var input1 = document.createElement('input');
            input1.type = 'hidden';
            input1.name = 'speichern_und_senden';
            input1.value = '1';
            document.getElementById('formnewslettervorlage').appendChild(input1);
            document.formnewslettervorlage.submit();
        {rdelim}
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('newsletterdraft') cBeschreibung=__('newsletterdraftdesc')}
<div id="content">
    <form name="formnewslettervorlagestd" id="formnewslettervorlagestd" method="post" action="newsletter.php" enctype="multipart/form-data">
        {$jtl_token}
        <div class="card settings">
            <div class="card-header">
                <div class="subheading1">{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($oNewslettervorlageStd->cName)}{$oNewslettervorlageStd->cName}{/if} {__('edit')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                {$jtl_token}
                <input name="newslettervorlagenstd" type="hidden" value="1">
                <input name="vorlage_std_speichern" type="hidden" value="1">
                <input name="tab" type="hidden" value="newslettervorlagenstd">

                {if isset($oNewslettervorlageStd->kNewslettervorlageStd) && $oNewslettervorlageStd->kNewslettervorlageStd > 0}
                    <input name="kNewslettervorlageStd" type="hidden" value="{$oNewslettervorlageStd->kNewslettervorlageStd}">
                {elseif isset($cPostVar_arr.kNewslettervorlageStd) && $cPostVar_arr.kNewslettervorlageStd > 0}
                    <input name="kNewslettervorlageStd" type="hidden" value="{$cPostVar_arr.kNewslettervorlageStd}">
                {/if}
                {if isset($oNewslettervorlageStd->kNewsletterVorlage) && $oNewslettervorlageStd->kNewsletterVorlage > 0}
                    <input name="kNewsletterVorlage" type="hidden" value="{$oNewslettervorlageStd->kNewsletterVorlage}">
                {elseif isset($cPostVar_arr.kNewslettervorlage) && $cPostVar_arr.kNewslettervorlage > 0}
                    <input name="kNewsletterVorlage" type="hidden" value="{$cPostVar_arr.kNewslettervorlage}">
                {/if}

                <div class="form-group form-row align-items-center{if isset($cPlausiValue_arr.cName)} form-error{/if}">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('newsletterdraftname')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input{if isset($cPlausiValue_arr.cName)} placeholder="{__('newsletterdraftFillOut')}"{/if} id="cName" name="cName" type="text" class="form-control" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($oNewslettervorlageStd->cName)}{$oNewslettervorlageStd->cName}{/if}">
                    </div>
                </div>

                <div class="form-group form-row align-items-center{if isset($cPlausiValue_arr.cBetreff)} form-error{/if}">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cBetreff">{__('subject')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input{if isset($cPlausiValue_arr.cBetreff)} placeholder="{__('newsletterdraftFillOut')}"{/if} id="cBetreff" name="cBetreff" type="text" class="form-control" value="{if isset($cPostVar_arr.cBetreff)}{$cPostVar_arr.cBetreff}{elseif isset($oNewslettervorlageStd->cBetreff)}{$oNewslettervorlageStd->cBetreff}{/if}">
                    </div>
                </div>
                <div class="form-group form-row align-items-center{if isset($cPlausiValue_arr.kKundengruppe_arr)} form-error{/if}">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppeSelect">{__('newslettercustomergrp')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select id="kKundengruppeSelect"
                                name="kKundengruppe[]"
                                multiple="multiple"
                                class="selectpicker custom-select {if !isset($cPlausiValue_arr.kKundengruppe_arr)}combo{/if}"
                                data-selected-text-format="count > 2"
                                data-size="7"
                                data-actions-box="true">
                            <option value="0"
                                {if isset($kKundengruppe_arr)}
                                    {foreach $kKundengruppe_arr as $kKundengruppe}
                                        {if $kKundengruppe == '0'}selected{/if}
                                    {/foreach}
                                {elseif isset($cPostVar_arr.kKundengruppe)}
                                    {foreach $cPostVar_arr.kKundengruppe as $kKundengruppe}
                                        {if $kKundengruppe == '0'}selected{/if}
                                    {/foreach}
                                {/if}>{__('newsletterNoAccount')}
                            </option>
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
                                    {/if}>{$customerGroup->getName()}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cArt">{__('newsletterdraftcharacter')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select id="cArt" name="cArt" class="custom-select combo">
                            <option {if isset($oNewslettervorlageStd->cArt) && $oNewslettervorlageStd->cArt === 'text/html'}selected{/if}>{__('textHtml')}</option>
                            <option {if isset($oNewslettervorlageStd->cArt) && $oNewslettervorlageStd->cArt === 'text'}selected{/if}>{__('text')}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="dTag">
                        {__('newsletterdraftdate')} ({__('newsletterdraftformat')}):
                    </label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <div class="row no-gutters">
                            <div class="col-sm-auto cols-12">
                                <select id="dTag" name="dTag" class="custom-select combo">
                                    {section name=dTag start=1 loop=32 step=1}
                                        {if $smarty.section.dTag.index < 10}
                                            <option value="0{$smarty.section.dTag.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[0] == $smarty.section.dTag.index} selected{/if}{else}{if $smarty.now|date_format:'%d' == $smarty.section.dTag.index} selected{/if}{/if}>
                                                0{$smarty.section.dTag.index}
                                            </option>
                                        {else}
                                            <option value="{$smarty.section.dTag.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[0] == $smarty.section.dTag.index} selected{/if}{else}{if $smarty.now|date_format:'%d' == $smarty.section.dTag.index} selected{/if}{/if}>
                                                {$smarty.section.dTag.index}
                                            </option>
                                        {/if}
                                    {/section}
                                </select>
                            </div>
                            <div class="col-sm-auto cols-12">
                                <select id="dMonat" name="dMonat" class="custom-select combo">
                                    {section name=dMonat start=1 loop=13 step=1}
                                        {if $smarty.section.dMonat.index < 10}
                                            <option value="0{$smarty.section.dMonat.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[1] == $smarty.section.dMonat.index} selected{/if}{else}{if $smarty.now|date_format:'%m' == $smarty.section.dMonat.index} selected{/if}{/if}>
                                                0{$smarty.section.dMonat.index}
                                            </option>
                                        {else}
                                            <option value="{$smarty.section.dMonat.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[1] == $smarty.section.dMonat.index} selected{/if}{else}{if $smarty.now|date_format:'%m' == $smarty.section.dMonat.index} selected{/if}{/if}>
                                                {$smarty.section.dMonat.index}
                                            </option>
                                        {/if}
                                    {/section}
                                </select>
                            </div>
                            <div class="col-sm-auto cols-12">
                                <select id="dJahr" name="dJahr" class="custom-select combo">
                                    {$Y = $smarty.now|date_format:'%Y'}
                                    {section name=dJahr start=$Y loop=($Y+2) step=1}
                                        <option value="{$smarty.section.dJahr.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[2] == $smarty.section.dJahr.index} selected{/if}{else}{if $smarty.now|date_format:'%Y' == $smarty.section.dJahr.index} selected{/if}{/if}>
                                            {$smarty.section.dJahr.index}
                                        </option>
                                    {/section}
                                </select>
                            </div>

                            <div class="col-sm-auto cols-12">
                                <select id="dStunde" name="dStunde" class="custom-select combo">
                                    {section name=dStunde start=0 loop=24 step=1}
                                        {if $smarty.section.dStunde.index < 10}
                                            <option value="0{$smarty.section.dStunde.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[3] == $smarty.section.dStunde.index} selected{/if}{else}{if $smarty.now|date_format:'%H' == $smarty.section.dStunde.index} selected{/if}{/if}>
                                                0{$smarty.section.dStunde.index}
                                            </option>
                                        {else}
                                            <option value="{$smarty.section.dStunde.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[3] == $smarty.section.dStunde.index} selected{/if}{else}{if $smarty.now|date_format:'%H' == $smarty.section.dStunde.index} selected{/if}{/if}>
                                                {$smarty.section.dStunde.index}
                                            </option>
                                        {/if}
                                    {/section}
                                </select>
                            </div>
                            <div class="col-sm-auto cols-12">
                                <select id="dMinute" name="dMinute" class="custom-select combo">
                                    {section name=dMinute start=0 loop=60 step=1}
                                        {if $smarty.section.dMinute.index < 10}
                                            <option value="0{$smarty.section.dMinute.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[4] == $smarty.section.dMinute.index} selected{/if}{else}{if $smarty.now|date_format:'%M' == $smarty.section.dMinute.index} selected{/if}{/if}>
                                                0{$smarty.section.dMinute.index}
                                            </option>
                                        {else}
                                            <option value="{$smarty.section.dMinute.index}"{if isset($oNewslettervorlageStd->oZeit->cZeit_arr) && $oNewslettervorlageStd->oZeit->cZeit_arr|@count > 0}{if $oNewslettervorlageStd->oZeit->cZeit_arr[4] == $smarty.section.dMinute.index} selected{/if}{else}{if $smarty.now|date_format:'%M' == $smarty.section.dMinute.index} selected{/if}{/if}>
                                                {$smarty.section.dMinute.index}
                                            </option>
                                        {/if}
                                    {/section}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kKampagneselect">{__('campaign')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select id="kKampagneselect" name="kKampagne" class="custom-select">
                            <option value="0"></option>
                            {foreach $oKampagne_arr as $oKampagne}
                                <option value="{$oKampagne->kKampagne}"{if (isset($oKampagne->kKampagne) && isset($oNewslettervorlageStd->kKampagn) && $oKampagne->kKampagne == $oNewslettervorlageStd->kKampagne) || (isset($cPostVar_arr.kKampagne) && isset($oKampagne->kKampagne) && $cPostVar_arr.kKampagne == $oKampagne->kKampagne)} selected{/if}>
                                    {$oKampagne->cName}
                                </option>
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
                               value="{if !empty($cPostVar_arr.cArtikel)}{$cPostVar_arr.cArtikel}{elseif isset($oNewslettervorlageStd->cArtikel)}{$oNewslettervorlageStd->cArtikel}{/if}">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
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
                               value="{if !empty($cPostVar_arr.cHersteller)}{$cPostVar_arr.cHersteller}{elseif isset($oNewslettervorlageStd->cHersteller)}{$oNewslettervorlageStd->cHersteller}{/if}">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        {include file='snippets/searchpicker_button.tpl' target='#manufacturerPicker-modal' title="{__('labelSearchManufacturer')}"}
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
                               value="{if !empty($cPostVar_arr.cKategorie)}{$cPostVar_arr.cKategorie}{elseif isset($oNewslettervorlageStd->cKategorie)}{$oNewslettervorlageStd->cKategorie}{/if}">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        {include file='snippets/searchpicker_button.tpl' target='#categoryPicker-modal' title="{__('labelSearchCategory')}"}
                    </div>
                </div>

                {if isset($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) && $oNewslettervorlageStd->oNewslettervorlageStdVar_arr|@count > 0}
                    {foreach $oNewslettervorlageStd->oNewslettervorlageStdVar_arr as $oNewslettervorlageStdVar}
                        {if $oNewslettervorlageStdVar->cTyp === 'BILD'}
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}">{$oNewslettervorlageStdVar->cName}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    {include file='tpl_inc/fileupload.tpl'
                                        fileID="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}"
                                        fileShowRemove=true
                                        fileInitialPreview="[
                                                {if isset($oNewslettervorlageStdVar->cInhalt) && $oNewslettervorlageStdVar->cInhalt|strlen > 0}
                                                    '<img class=\"img-fluid\" src=\"{$oNewslettervorlageStdVar->cInhalt}?={$nRand}\" />'
                                                {/if}
                                                ]"
                                            }
                                </div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cLinkURL">{__('newsletterPicLink')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input id="cLinkURL" name="cLinkURL" type="text" class="form-control" value="{if !empty($cPostVar_arr.cLinkURL)}{$cPostVar_arr.cLinkURL}{elseif !empty($oNewslettervorlageStdVar->cLinkURL)}{$oNewslettervorlageStdVar->cLinkURL}{/if}" />
                                </div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cAltTag">{__('newsletterAltTag')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" id="cAltTag" name="cAltTag" type="text" value="{if !empty($cPostVar_arr.cAltTag)}{$cPostVar_arr.cAltTag}{elseif !empty($oNewslettervorlageStdVar->cAltTag)}{$oNewslettervorlageStdVar->cAltTag}{/if}" />
                                </div>
                            </div>
                        {elseif $oNewslettervorlageStdVar->cTyp === 'TEXT'}
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}">{$oNewslettervorlageStdVar->cName}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <textarea id="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}" class="form-control codemirror smarty" name="kNewslettervorlageStdVar_{$oNewslettervorlageStdVar->kNewslettervorlageStdVar}" style="width: 500px; height: 400px;">
                                        {if isset($oNewslettervorlageStdVar->cInhalt) && $oNewslettervorlageStdVar->cInhalt|strlen > 0}{$oNewslettervorlageStdVar->cInhalt}{/if}
                                    </textarea>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                {/if}
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        {if (isset($oNewslettervorlageStd->kNewsletterVorlage) && $oNewslettervorlageStd->kNewsletterVorlage > 0) || (isset($cPostVar_arr.kNewslettervorlage) && $cPostVar_arr.kNewslettervorlage > 0)}
                            <a class="btn btn-outline-primary btn-block" href="newsletter.php?tab=newslettervorlagen&token={$smarty.session.jtl_token}">
                                {__('cancelWithIcon')}
                            </a>
                        {else}
                            <a class="btn btn-outline-primary btn-block" href="newsletter.php?tab=newslettervorlagenstd&token={$smarty.session.jtl_token}">
                                {__('cancelWithIcon')}
                            </a>
                        {/if}
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button class="btn btn-primary btn-block" name="speichern" type="submit" value="{__('save')}">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {if !empty($oNewslettervorlageStd->kNewsletterVorlage)}
        {getRevisions type='newsletterstd' key=$oNewslettervorlageStd->kNewsletterVorlage show=['cInhalt'] secondary=true data=$revisionData}
    {/if}
</div>
