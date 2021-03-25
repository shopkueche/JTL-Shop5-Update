{if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}
    {assign var=subheading value=__('aaGroupEdit')}
    {assign var=cTitel value=__('auswahlassistent')|cat:' - '|cat:$subheading}
{else}
    {assign var=subheading value=__('aaGroup')}
    {assign var=cTitel value=__('auswahlassistent')|cat:' - '|cat:$subheading}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('auswahlassistentDesc') cDokuURL=__('auswahlassistentURL')}

<div id="content">
    {if !isset($noModule) || !$noModule}
        <form class="settings" method="post" action="auswahlassistent.php">
            {$jtl_token}
            <input name="kSprache" type="hidden" value="{$languageID}">
            <input name="tab" type="hidden" value="gruppe">
            <input name="a" type="hidden" value="addGrp">
            {if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}
                <input class="form-control" name="kAuswahlAssistentGruppe" type="hidden"
                       value="{if isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0}{$kAuswahlAssistentGruppe}{else}{$oGruppe->kAuswahlAssistentGruppe}{/if}">
            {/if}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{$subheading}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.cName)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('name')}</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input name="cName" id="cName" type="text"
                                   class="form-control"
                                   value="{if isset($cPost_arr.cName)}{$cPost_arr.cName}{elseif isset($oGruppe->cName)}{$oGruppe->cName}{/if}">
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc="{__('hintName')}"}</div>
                    </div>

                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cBeschreibung">{__('description')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cBeschreibung" name="cBeschreibung"
                                      class="form-control description">{if isset($cPost_arr.cBeschreibung)}{$cPost_arr.cBeschreibung}{elseif isset($oGruppe->cBeschreibung)}{$oGruppe->cBeschreibung}{/if}</textarea>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc="{__('hintDesc')}"}</div>
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
                                renderItemCb:      renderCategoryItem,
                                onApply:           onApplySelectedCategories,
                                selectedKeysInit:  $('#assign_categories_list').val().split(';').filter(function (i) { return i !== ''; })
                            });
                        });
                        function renderCategoryItem(item)
                        {
                            return '<p class="list-group-item-text">' + item.cName + '</p>';
                        }
                        function onApplySelectedCategories(selected)
                        {
                            $('#assign_categories_list').val(selected.join(';'));
                        }
                    </script>

                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.cOrt)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="assign_categories_list">{__('category')}
                            {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie != 3} <span class="fillout">{__('aaKatSyntax')}</span>{/if}
                            {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie == 3} <span class="fillout">{__('aaKatTaken')}</span>{/if}:
                        </label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input name="cKategorie" id="assign_categories_list" type="text"
                                   class="form-control"
                                   value="{if isset($cPost_arr.cKategorie)}{$cPost_arr.cKategorie}{elseif isset($oGruppe->cKategorie)}{$oGruppe->cKategorie}{/if}">
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {include file='snippets/searchpicker_button.tpl' target='#categoryPicker-modal' title="{__('questionCatInGroup')}"}
                        </div>
                    </div>

                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.cOrt)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right"for="kLink_arr">{__('aaSpecialSite')}
                            {if isset($cPlausi_arr.kLink_arr)} <span class="fillout">{__('aaLinkTaken')}</span>{/if}:
                        </label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            {if $oLink_arr|count > 0}
                                <select id="kLink_arr" name="kLink_arr[]"  class="custom-select" multiple>
                                    {foreach $oLink_arr as $oLink}
                                        {assign var=bAOSelect value=false}
                                        {if isset($oGruppe->oAuswahlAssistentOrt_arr) && $oGruppe->oAuswahlAssistentOrt_arr|@count > 0}
                                            {foreach $oGruppe->oAuswahlAssistentOrt_arr as $oAuswahlAssistentOrt}
                                                {if $oLink->kLink == $oAuswahlAssistentOrt->kKey && $oAuswahlAssistentOrt->cKey == $smarty.const.AUSWAHLASSISTENT_ORT_LINK}
                                                    {assign var=bAOSelect value=true}
                                                {/if}
                                            {/foreach}
                                        {elseif isset($cPost_arr.kLink_arr) && $cPost_arr.kLink_arr|@count > 0}
                                            {foreach $cPost_arr.kLink_arr as $kLink}
                                                {if $kLink == $oLink->kLink}
                                                    {assign var=bAOSelect value=true}
                                                {/if}
                                            {/foreach}
                                        {/if}
                                        <option value="{$oLink->kLink}"{if $bAOSelect} selected{/if}>{$oLink->cName}</option>
                                    {/foreach}
                                </select>
                            {else}
                                <input type="text" disabled value="{__('noSpecialPageAvailable')}" class="form-control" />
                            {/if}
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {getHelpDesc cDesc="{__('hintSpecialPage')}"}
                        </div>
                    </div>

                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.cOrt)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nStartseite">{__('startPage')}
                            {if isset($cPlausi_arr.nStartseite)} <span class="fillout">{__('aaStartseiteTaken')}</span>{/if}:
                        </label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="nStartseite" name="nStartseite"  class="custom-select">
                                <option value="0"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 0) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 0)} selected{/if}>
                                    {__('no')}
                                </option>
                                <option value="1"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 1) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 1)} selected{/if}>
                                    {__('yes')}
                                </option>
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc="{__('hintGroupOnHome')}"}</div>
                    </div>

                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('active')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="nAktiv" class="custom-select" name="nAktiv">
                                <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 1)} selected{/if}>
                                    {__('yes')}
                                </option>
                                <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 0)} selected{/if}>
                                    {__('no')}
                                </option>
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {getHelpDesc cDesc="{__('hintShowCheckbox')}"}
                        </div>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a href="auswahlassistent.php" class="btn btn-outline-primary btn-block">{__('cancelWithIcon')}</a>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="speicherGruppe" type="submit" value="save" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="ajax_list_picker" class="ajax_list_picker categories">{include file='tpl_inc/popup_kategoriesuche.tpl'}</div>
        </form>
    {else}
        <div class="alert alert-danger">{__('noModuleAvailable')}</div>
    {/if}
</div>

{include file='tpl_inc/footer.tpl'}
