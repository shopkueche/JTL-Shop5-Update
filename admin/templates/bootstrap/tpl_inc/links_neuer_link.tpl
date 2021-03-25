<script type="text/javascript">
    $(function () {
        $('#lang').on('change', function () {
            var iso = $('#lang option:selected').val();
            $('.iso_wrapper').slideUp();
            $('#iso_' + iso).slideDown();
            return false;
        });

        $('input[name="nLinkart"]').on('change', function () {
            var lnk = $('input[name="nLinkart"]:checked').val();
            if (lnk == '1') {
                $('#option_isActive').slideDown("slow");
            } else {
                $('#option_isActive').slideUp("slow");
                $('#option_isActive select').val(1);
            }
        }).trigger('change');
    });
    $(window).on('load', function () {
        $('#specialLinkType, #cKundengruppen').change(function () {
            ioCall('isDuplicateSpecialLink', [
                    parseInt($('#specialLinkType').val()),
                    parseInt($('input[name="kLink"]').val()) || 0,
                    $('#cKundengruppen').val()
                ],
                function (result) {
                    if (result) {
                        $('#specialLinkType-error').removeClass('hidden-soft');
                    } else {
                        $('#specialLinkType-error').addClass('hidden-soft');
                    }
                }
            );
        }).trigger('change');
    });
</script>
{if $Link->getID() > 0 && !empty($Link->getName())}
    {assign var=description value=$Link->getName()|cat:' (ID '|cat:$Link->getID()|cat:')'}
{else}
    {assign var=description value=''}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=__('newLinks') cBeschreibung=$description}
<div id="content">
    <div id="settings">
        <form id="create_link" name="link_erstellen" method="post" action="links.php" enctype="multipart/form-data">
            {$jtl_token}
            <input type="hidden" name="action" value="create-or-update-link" />
            <input type="hidden" name="kLinkgruppe" value="{$Link->getLinkGroupID()}" />
            <input type="hidden" name="kLink" value="{if $Link->getID() > 0}{$Link->getID()}{/if}" />
            <input type="hidden" name="kPlugin" value="{if $Link->getPluginID() > 0}{$Link->getPluginID()}{/if}" />
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('general')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center{if isset($xPlausiVar_arr.cName)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('name')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input required type="text" name="cName" id="cName" class="form-control" value="{if isset($xPostVar_arr.cName) && $xPostVar_arr.cName}{$xPostVar_arr.cName}{elseif !empty($Link->getDisplayName())}{$Link->getDisplayName()}{/if}" tabindex="1" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center{if isset($xPlausiVar_arr.nLinkart) || isset($xPlausiVar_arr.nSpezialseite)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right">{__('linkType')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        {if $Link->getPluginID() > 0}
                            <p class="multi_input">
                                <input type="hidden" name="nLinkart" value="25" />
                                <input type="radio" id="nLink3" name="nLinkart" checked="checked" disabled="disabled" />
                                <label for="nLink3">{__('linkToSpecalPage')}:</label>
                                <select class="custom-select" id="specialLinkType" name="nSpezialseite" disabled="disabled">
                                    <option selected="selected" value="0">{__('plugin')}</option>
                                </select>
                            </p>
                        {else}
                            <p class="multi_input" style="margin-top: 10px;">
                                <input type="radio" id="nLink1" name="nLinkart" value="1" tabindex="2" {if isset($xPostVar_arr.nLinkart) && (int)$xPostVar_arr.nLinkart === 1}checked{elseif $Link->getLinkType() === 1}checked{/if} />
                                <label for="nLink1">{__('linkWithOwnContent')}:</label>
                            </p>
                            <p class="multi_input">
                                <input type="radio" id="nLink2" name="nLinkart" value="2" onclick="$('#nLinkInput2').val('http://')" tabindex="3" {if isset($xPostVar_arr.nLinkart) && (int)$xPostVar_arr.nLinkart === 2}checked{elseif $Link->getLinkType() === 2}checked{/if} />
                                <label for="nLink2">{__('linkToExternalURL')} {__('createWithSearchEngineName')}:</label>
                            </p>
                            <p class="multi_input" style="margin-bottom: 10px;">
                                <input type="radio" id="nLink3" name="nLinkart" value="3" {if isset($xPostVar_arr.nLinkart) && (int)$xPostVar_arr.nLinkart === 3}checked{elseif $Link->getLinkType() > 2}checked{/if} />
                                <label for="nLink3">{__('linkToSpecalPage')}:</label>
                                <select class="custom-select" id="specialLinkType" name="nSpezialseite">
                                    <option value="0">{__('choose')}</option>
                                    {foreach $specialPages as $specialPage}
                                        <option value="{$specialPage->nLinkart}" {if isset($xPostVar_arr.nSpezialseite) && $xPostVar_arr.nSpezialseite === $specialPage->nLinkart}selected{elseif $Link->getLinkType() === $specialPage->nLinkart}selected{/if}>{__($specialPage->cName)}</option>
                                    {/foreach}
                                </select>
                                <span id="specialLinkType-error" class="hidden-soft error"> <i title="{__('isDuplicateSpecialLink')}" class="fal fa-exclamation-triangle error"></i></span>
                            </p>
                        {/if}
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center{if isset($xPlausiVar_arr.cKundengruppen)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cKundengruppen">{__('restrictedToCustomerGroups')}:</label>
                        {$activeGroups = $Link->getCustomerGroups()}
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select required name="cKundengruppen[]"
                                    class="selectpicker custom-select"
                                    multiple="multiple"
                                    size="6"
                                    id="cKundengruppen"
                                    data-selected-text-format="count > 2"
                                    data-size="7">
                                <option value="-1"
                                    {if isset($Link->getID()) && $Link->getID() > 0 && count($activeGroups) === 0} selected
                                    {elseif isset($xPostVar_arr.cKundengruppen)}
                                        {foreach $xPostVar_arr.cKundengruppen as $cPostKndGrp}
                                            {if (int)$cPostKndGrp === -1} selected{/if}
                                        {/foreach}
                                    {elseif !$Link->getID() > 0} selected{/if}
                                >{__('all')}</option>
                                <option data-divider="true"></option>
                                {foreach $kundengruppen as $kundengruppe}
                                    {assign var=kKundengruppe value=(int)$kundengruppe->kKundengruppe}
                                    {assign var=postkndgrp value=0}
                                    {if isset($xPostVar_arr.cKundengruppen)}
                                        {foreach $xPostVar_arr.cKundengruppen as $cPostKndGrp}
                                            {if $cPostKndGrp == $kKundengruppe}{assign var=postkndgrp value=1}{/if}
                                        {/foreach}
                                    {/if}
                                    <option value="{$kundengruppe->kKundengruppe}"
                                        {if isset($xPostVar_arr) && isset($postkndgrp) && $postkndgrp == 1}selected
                                        {elseif in_array($kKundengruppe, $activeGroups, true)}selected
                                        {/if}
                                    >{$kundengruppe->cName}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('multipleChoice')}</div>
                    </div>
                    <div class="form-group form-row align-items-center" id="option_isActive">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="bIsActive">{__('active')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="custom-select" type="selectbox" name="bIsActive" id="bIsActive">
                                <option value="1" {if $Link->getIsEnabled() || (isset($xPostVar_arr.bIsActive) && $xPostVar_arr.bIsActive === '1')}selected{/if}>{__('activated')}</option>
                                <option value="0" {if !$Link->getIsEnabled() || (isset($xPostVar_arr.bIsActive) && $xPostVar_arr.bIsActive === '0')}selected{/if}>{__('deactivated')}</option>
                            </select>
                        </div>
                    </div>
                    {if !isset($Link->getLinkType()) || $Link->getLinkType() != LINKTYP_LOGIN}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cSichtbarNachLogin">{__('visibleAfterLogin')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" class="form-control2" type="checkbox" name="cSichtbarNachLogin" id="cSichtbarNachLogin" value="Y" {if $Link->getVisibleLoggedInOnly() === true || (isset($xPostVar_arr.cSichtbarNachLogin) && $xPostVar_arr.cSichtbarNachLogin)}checked{/if} />
                                <label class="custom-control-label" for="cSichtbarNachLogin"></label>
                            </div>
                        </div>
                    </div>
                    {/if}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="bSSL">SSL:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="bSSL" class="custom-select" name="bSSL">
                                <option value="0"{if $Link->getSSL() === false || (isset($xPostVar_arr.bSSL) && ($xPostVar_arr.bSSL == 0 || $xPostVar_arr.bSSL == 1))} selected="selected"{/if}>{__('standard')}</option>
                                <option value="2"{if $Link->getSSL() === true || (isset($xPostVar_arr.bSSL) && $xPostVar_arr.bSSL == 2)} selected="selected"{/if}>{__('forced')}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cNoFollow">{__('noFollow')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" class="form-control2" type="checkbox" name="cNoFollow" id="cNoFollow" value="Y" {if $Link->getNoFollow() === true || (isset($xPostVar_arr.cNoFollow) && $xPostVar_arr.cNoFollow)}checked{/if} />
                                <label class="custom-control-label" for="cNoFollow"></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nSort">{__('sortNo')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="text" name="nSort" id="nSort" value="{if isset($xPostVar_arr.nSort) && $xPostVar_arr.nSort}{$xPostVar_arr.nSort}{elseif $Link->getSort()}{$Link->getSort()}{/if}" tabindex="6" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="Bilder_0">{__('images')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            {include file='tpl_inc/fileupload.tpl'
                                fileID='Bilder_0'
                                fileName='Bilder[]'
                                fileMaxSize=1000
                                fileIsSingle=false
                                fileInitialPreview="[
                                    {if !empty($cDatei_arr)}
                                        {foreach $cDatei_arr as $cDatei}
                                            '{$cDatei->cURL}<a href=\"links.php?action=edit-link&kLink={$Link->getID()}&token={$smarty.session.jtl_token}&delpic=1&cName={$cDatei->cNameFull}{if isset($Link->getPluginID()) && $Link->getPluginID() > 0}{$Link->getPluginID()}{/if}\"><i class=\"fas fa-trash\"></i></a>',
                                        {/foreach}
                                    {/if}
                                    ]"
                                fileInitialPreviewConfig="[
                                        {if !empty($cDatei_arr)}
                                            {foreach $cDatei_arr as $cDatei}
                                                {
                                                caption: '$#{$cDatei->cName}#$',
                                                width:   '120px'
                                                },
                                            {/foreach}
                                        {/if}
                                    ]"
                            }
                        </div>

                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="bIsFluid">{__('bIsFluidText')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" class="form-control2" type="checkbox" name="bIsFluid" id="bIsFluid" value="1" {if $Link->getIsFluid() === true || (isset($xPostVar_arr.bIsFluid) && $xPostVar_arr.bIsFluid === '1')}checked{/if} />
                                <label class="custom-control-label" for="bIsFluid"></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cIdentifier">{__('cIdentifierText')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="text" name="cIdentifier" id="cIdentifier" value="{if $Link->getIdentifier()}{$Link->getIdentifier()}{elseif isset($xPostVar_arr.bIsFluid)}$xPostVar_arr.bIsFluid{/if}" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="lang">{__('language')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="custom-select" name="cISO" id="lang">
                                {foreach $availableLanguages as $language}
                                    <option value="{$language->getIso()}" {if $language->getShopDefault() === 'Y'}selected="selected"{/if}>{$language->getLocalizedName()} {if $language->getShopDefault() === 'Y'}({__('standard')}){/if}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {foreach $availableLanguages as $language}
                {assign var=cISO value=$language->getIso()}
                {assign var=langID value=$language->getId()}
                <div id="iso_{$cISO}" class="iso_wrapper{if !$language->isShopDefault()} hidden-soft{/if}">
                    <div class="card">
                        <div class="card-header">
                            <div class="subheading1">{__('metaSeo')} ({$language->getLocalizedName()})</div>
                            <hr class="mb-n3">
                        </div>
                        <div class="card-body">
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cName_{$cISO}">{__('showedName')}:</label>
                                {assign var=cName_ISO value='cName_'|cat:$cISO}
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cName_{$cISO}" id="cName_{$cISO}" value="{if isset($xPostVar_arr.$cName_ISO) && $xPostVar_arr.$cName_ISO}{$xPostVar_arr.$cName_ISO}{elseif !empty($Link->getName($langID))}{$Link->getName($langID)}{/if}" tabindex="7" />
                                </div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cSeo_{$cISO}">{__('linkSeo')}:</label>
                                {assign var=cSeo_ISO value="cSeo_"|cat:$cISO}
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cSeo_{$cISO}" id="cSeo_{$cISO}" value="{if isset($xPostVar_arr.$cSeo_ISO) && $xPostVar_arr.$cSeo_ISO}{$xPostVar_arr.$cSeo_ISO}{elseif !empty($Link->getSEO($langID))}{$Link->getSEO($langID)}{/if}" tabindex="7" />
                                </div>
                            </div>
                            {assign var=cTitle_ISO value='cTitle_'|cat:$cISO}
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cTitle_{$cISO}">{__('linkTitle')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cTitle_{$cISO}" id="cTitle_{$cISO}" value="{if isset($xPostVar_arr.$cTitle_ISO) && $xPostVar_arr.$cTitle_ISO}{$xPostVar_arr.$cTitle_ISO}{elseif !empty($Link->getTitle($langID))}{$Link->getTitle($langID)}{/if}" tabindex="8" />
                                </div>
                                <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('titleDesc')}</div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                {assign var=cContent_ISO value='cContent_'|cat:$cISO}
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cContent_{$cISO}">{__('content')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <textarea class="form-control ckeditor" id="cContent_{$cISO}" name="cContent_{$cISO}" rows="10" cols="40">{if isset($xPostVar_arr.$cContent_ISO) && $xPostVar_arr.$cContent_ISO}{$xPostVar_arr.$cContent_ISO}{elseif !empty($Link->getContent($langID))}{$Link->getContent($langID)}{/if}</textarea>
                                </div>
                                <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('titleDesc')}</div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                {assign var=cMetaTitle_ISO value='cMetaTitle_'|cat:$cISO}
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cMetaTitle_{$cISO}">{__('metaTitle')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cMetaTitle_{$cISO}" id="cMetaTitle_{$cISO}" value="{if isset($xPostVar_arr.$cMetaTitle_ISO) && $xPostVar_arr.$cMetaTitle_ISO}{$xPostVar_arr.$cMetaTitle_ISO}{elseif !empty($Link->getMetaTitle($langID))}{$Link->getMetaTitle($langID)}{/if}" tabindex="9" />
                                </div>
                                <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('metaTitleDesc')}</div>
                            </div>
                            <div class="form-group form-row align-items-center">
                            {assign var=cMetaKeywords_ISO value='cMetaKeywords_'|cat:$cISO}
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cMetaKeywords_{$cISO}">{__('metaKeywords')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cMetaKeywords_{$cISO}" id="cMetaKeywords_{$cISO}" value="{if isset($xPostVar_arr.$cMetaKeywords_ISO) && $xPostVar_arr.$cMetaKeywords_ISO}{$xPostVar_arr.$cMetaKeywords_ISO}{elseif !empty($Link->getMetaKeyword($langID))}{$Link->getMetaKeyword($langID)}{/if}" tabindex="9" />
                                </div>
                                <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('metaKeywordsDesc')}</div>
                            </div>
                            <div class="form-group form-row align-items-center">
                                {assign var=cMetaDescription_ISO value='cMetaDescription_'|cat:$cISO}
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cMetaDescription_{$cISO}">{__('metaDescription')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cMetaDescription_{$cISO}" id="cMetaDescription_{$cISO}" value="{if isset($xPostVar_arr.$cMetaDescription_ISO) && $xPostVar_arr.$cMetaDescription_ISO}{$xPostVar_arr.$cMetaDescription_ISO}{elseif !empty($Link->getMetaDescription($langID))}{$Link->getMetaDescription($langID)}{/if}" tabindex="9" />
                                </div>
                                <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('metaDescriptionDesc')}</div>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a class="btn btn-outline-primary btn-block" href="links.php">
                            {__('cancelWithIcon')}
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" name="continue" value="1" class="btn btn-outline-primary btn-block" id="save-and-continue">
                            <i class="fal fa-save"></i> {__('newLinksSaveContinueEdit')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" value="{__('newLinksSave')}" class="btn btn-primary btn-block">
                            <i class="far fa-save"></i> {__('newLinksSave')}
                        </button>
                    </div>
                </div>
            </div>
        </form>
        {if isset($Link->getID())}
            {getRevisions type='link' key=$Link->getID() show=['cContent'] secondary=true data=$Link->getData()}
        {/if}
    </div>
</div>
