<script type="text/javascript">
    var file2large = false;
    {literal}

    $(document).ready(function () {
        $('#lang').on('change', function () {
            var iso = $('#lang option:selected').val();
            $('.iso_wrapper').slideUp();
            $('#iso_' + iso).slideDown();
            return false;
        });
        $('form input[type=file]').on('change', function(e){
            $('form div.alert').slideUp();
            var filesize= this.files[0].size;
            {/literal}
            var maxsize = {$nMaxFileSize};
            {literal}
            if (filesize >= maxsize) {
                $(this).after('<div class="alert alert-danger"><i class="fal fa-exclamation-triangle"></i> {/literal}{__('errorUploadSizeLimit')}{literal}</div>').slideDown();
                file2large = true;
            } else {
                $(this).closest('div.alert').slideUp();
                file2large = false;
            }
        });

    });

    function checkfile(e){
        e.preventDefault();
        if (!file2large){
            document.news.submit();
        }
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('category')}
<div id="content">
    <form name="news" method="post" action="news.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="news_kategorie_speichern" value="1" />
        <input type="hidden" name="tab" value="kategorien" />
        {if $oNewsKategorie->getID() > 0}
            <input type="hidden" name="newskategorie_edit_speichern" value="1" />
            <input type="hidden" name="kNewsKategorie" value="{$oNewsKategorie->getID()}" />
            {if isset($cSeite)}
                <input type="hidden" name="s3" value="{$cSeite}" />
            {/if}
        {/if}
        <div class="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{if $oNewsKategorie->getID() > 0}{__('newsCatEdit')} ({__('id')} {$oNewsKategorie->getID()}){else}{__('newsCatCreate')}{/if}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive">
                    <div class="card-body" id="formtable">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="kParent">{__('newsCatParent')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" id="kParent" name="kParent">
                                    <option value="0"> - {__('mainCategory')} - </option>
                                    {if $oNewsKategorie->getParentID()}
                                        {assign var=selectedCat value=$oNewsKategorie->getParentID()}
                                    {else}
                                        {assign var=selectedCat value=0}
                                    {/if}
                                    {include file='snippets/newscategories_recursive.tpl' i=0 selectedCat=$selectedCat}
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nSort">{__('newsCatSort')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control{if !empty($cPlausiValue_arr.nSort)} error{/if}" id="nSort" name="nSort" type="text" value="{$oNewsKategorie->getSort()}" />
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('active')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" id="nAktiv" name="nAktiv">
                                    <option value="1"{if $oNewsKategorie->getIsActive() === true} selected{/if}>
                                        {__('yes')}
                                    </option>
                                    <option value="0"{if $oNewsKategorie->getIsActive() === false} selected{/if}>
                                        {__('no')}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="previewImage">{__('preview')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {include file='tpl_inc/fileupload.tpl'
                                    fileID='previewImage'
                                    fileShowRemove=true
                                    fileMaxSize=2097152
                                    fileInitialPreview="[
                                            {if !empty($oNewsKategorie->getPreviewImage())}
                                                '<img src=\"{$shopURL}/{$oNewsKategorie->getPreviewImage()}\" class=\"mb-3\" />'
                                            {/if}
                                        ]"
                                }
                            </div>
                        </div>
                        {if $files|@count > 0}
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right">{__('newsPics')}:</label>
                                <div>
                                    {foreach $files as $file}
                                        <div class="well col-xs-3">
                                            <div class="thumbnail"><img src="{$file->cURLFull}" alt=""></div>
                                            <label>{__('link')}: </label>
                                            <div class="input-group">
                                                <input class="form-control" type="text" disabled="disabled" value="$#{$file->cName}#$">
                                                <div class="input-group-addon">
                                                    <a href="news.php?news=1&newskategorie_editieren=1&kNewsKategorie={$oNewsKategorie->getID()}&delpic={$oDatei->cName}&token={$smarty.session.jtl_token}" title="{__('delete')}"><i class="fas fa-trash-alt"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        {/if}
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
            </div>
            {foreach $availableLanguages as $language}
                {assign var=cISO value=$language->getIso()}
                {assign var=langID value=$language->getId()}
                <input type="hidden" name="lang_{$cISO}" value="{$langID}">
                <div id="iso_{$cISO}" class="iso_wrapper{if !$language->isShopDefault()} hidden-soft{/if}">
                    <div class="card">
                        <div class="card-header">
                            <div class=subheading1>{__('metaSeo')} ({$language->getLocalizedName()})</div>
                            <hr class="mb-n3">
                        </div>
                        <div class="table-responsive">
                            <div class="card-body" id="formtable">
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cName_{$cISO}">{__('name')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control{if !empty($cPlausiValue_arr.cName)} error{/if}" id="cName_{$cISO}" name="cName_{$cISO}" type="text" value="{if $oNewsKategorie->getName($langID) !== ''}{$oNewsKategorie->getName($langID)}{/if}" />{if isset($cPlausiValue_arr.cName) && $cPlausiValue_arr.cName == 2} {__('newsAlreadyExists')}{/if}
                                    </div>
                                </div>
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cSeo_{$cISO}">{__('newsSeo')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control{if !empty($cPlausiValue_arr.cSeo)} error{/if}" id="cSeo_{$cISO}" name="cSeo_{$cISO}" type="text" value="{if $oNewsKategorie->getSEO($langID) !== ''}{$oNewsKategorie->getSEO($langID)}{/if}" />
                                    </div>
                                </div>
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cMetaTitle_{$cISO}">{__('newsMetaTitle')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control{if !empty($cPlausiValue_arr.cMetaTitle)} error{/if}" id="cMetaTitle_{$cISO}" name="cMetaTitle_{$cISO}" type="text" value="{$oNewsKategorie->getMetaTitle($langID)}" />
                                    </div>
                                </div>
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cMetaDescription_{$cISO}">{__('newsMetaDescription')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input class="form-control{if !empty($cPlausiValue_arr.cMetaDescription)} error{/if}" id="cMetaDescription_{$cISO}" name="cMetaDescription_{$cISO}" type="text" value="{$oNewsKategorie->getMetaDescription($langID)}" />
                                    </div>
                                </div>
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cBeschreibung_{$cISO}">{__('description')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <textarea id="cBeschreibung_{$cISO}" class="ckeditor" name="cBeschreibung_{$cISO}" rows="15" cols="60">{$oNewsKategorie->getDescription($langID)}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <a class="btn btn-outline-primary btn-block" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}">
                                        <i class="fa fa-exclamation"></i> {__('Cancel')}
                                    </a>
                                </div>
                                <div class=" col-sm-6 col-xl-auto">
                                    <button name="speichern" type="button" value="{__('save')}" onclick="document.news.submit();" class="btn btn-primary btn-block">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </form>
</div>
