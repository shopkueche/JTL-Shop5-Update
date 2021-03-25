{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('boxen') cBeschreibung=__('boxenDesc') cDokuURL=__('boxenURL')}

{include file='tpl_inc/searchpicker_modal.tpl'
    searchPickerName='articlePicker'
    modalTitle="{__('titleChooseProducts')}"
    searchInputLabel="{__('labelSearchProduct')}"
}
{include file='tpl_inc/searchpicker_modal.tpl'
    searchPickerName='categoryPicker'
    modalTitle="{__('titleChooseCategory')}"
    searchInputLabel="{__('labelSearchCategory')}"
}
{include file='tpl_inc/searchpicker_modal.tpl'
    searchPickerName='manufacturerPicker'
    modalTitle="{__('titleChooseManufacturer')}"
    searchInputLabel="{__('labelSearchManufacturer')}"
}
{include file='tpl_inc/searchpicker_modal.tpl'
    searchPickerName='pagePicker'
    modalTitle="{__('titleChoosePage')}"
    searchInputLabel="{__('labelSearchPage')}"
}

<script>
    $(function () {
        articlePicker = new SearchPicker({
            searchPickerName:  'articlePicker',
            getDataIoFuncName: 'getProducts',
            keyName:           'kArtikel',
            renderItemCb:      renderItemName
        });
        categoryPicker = new SearchPicker({
            searchPickerName:  'categoryPicker',
            getDataIoFuncName: 'getCategories',
            keyName:           'kKategorie',
            renderItemCb:      renderItemName
        });
        manufacturerPicker = new SearchPicker({
            searchPickerName:  'manufacturerPicker',
            getDataIoFuncName: 'getManufacturers',
            keyName:           'kHersteller',
            renderItemCb:      renderItemName
        });
        pagePicker = new SearchPicker({
            searchPickerName:  'pagePicker',
            getDataIoFuncName: 'getPages',
            keyName:           'kLink',
            renderItemCb:      renderItemName
        });
    });

    function renderItemName (item)
    {
        return '<p class="list-group-item-text">' + item.cName + '</p>';
    }

    function openFilterPicker (picker, kBox)
    {
        picker
            .setOnApplyBefore(
                function () { onApplyBeforeFilterPicker(kBox) }
            )
            .setOnApply(
                function (selectedKeys, selectedItems) { onApplyFilterPicker(kBox, selectedKeys, selectedItems) }
            )
            .setSelection($('#box-filter-' + kBox).val().split(',').filter(Boolean))
            .show();
    }

    function onApplyBeforeFilterPicker (kBox)
    {
        $('#box-active-filters-' + kBox)
            .empty()
            .append(
                '<li class="selected-item"><i class="fa fa-spinner fa-pulse"></i></li>'
            );
    }

    function onApplyFilterPicker (kBox, selectedKeys, selectedItems)
    {
        var $activeFilterList = $('#box-active-filters-' + kBox);

        $('#box-filter-' + kBox).val(selectedKeys.join(','));
        $activeFilterList.empty();

        selectedItems.forEach(function (item) {
            $activeFilterList.append(
                '<li class="selected-item"><i class="fa fa-filter"></i> ' + item.cName + '</li>'
            );
        });
    }

    function confirmDelete(cName)
    {
        return confirm('{__('confirmDeleteBox')}'.replace('%s', cName));
    }
</script>

<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-items">
                    <a class="nav-link {if $cTab === '' || $cTab === 'uebersicht'} active{/if}" data-toggle="tab" role="tab" href="#overview">
                        {__('boxen')}
                    </a>
                </li>
                <li class="nav-items">
                    <a class="nav-link {if $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#config">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="overview" class="tab-pane fade{if $cTab === '' || $cTab === 'uebersicht'} active show{/if}">
                {if $invisibleBoxes|count > 0}
                    <div class="alert alert-danger">{__('warningInvisibleBoxes')}</div>
                    <form action="boxen.php" method="post" class="block">
                        {$jtl_token}
                        <div class="card editorInner">
                            <div class="card-header">
                                <div class="subheading1">{__('invisibleBoxes')}</div>
                            </div>
                            <div class="table-responsive card-body">
                                <table class="table table-align-top">
                                    <tr class="boxRow">
                                        <th class="check">&nbsp;</th>
                                        <th>
                                            <strong>{__('boxTitle')}</strong>
                                        </th>
                                        <th>
                                            <strong>{__('boxLabel')}</strong>
                                        </th>
                                        <th>
                                            <strong>{__('boxTemplate')}</strong>
                                        </th>
                                        <th>
                                            <strong>{__('position')}</strong>
                                        </th>
                                    </tr>
                                    {foreach $invisibleBoxes as $invisibleBox}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kInvisibleBox[]" type="checkbox" value="{$invisibleBox->kBox}" id="kInvisibleBox-{$invisibleBox@index}">
                                                    <label class="custom-control-label" for="kInvisibleBox-{$invisibleBox@index}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <label for="kInvisibleBox-{$invisibleBox@index}">{$invisibleBox->cTitel}</label>
                                            </td>
                                            <td>
                                                {$invisibleBox->cName}
                                            </td>
                                            <td>
                                                {$invisibleBox->cTemplate}
                                            </td>
                                            <td>
                                                {$invisibleBox->ePosition}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    <tr>
                                        <td class="check">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                                <label class="custom-control-label" for="ALLMSGS"></label>
                                            </div>
                                        </td>
                                        <td colspan="4" class="text-left"><label for="ALLMSGS">{__('globalSelectAll')}</label></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                <button name="action" type="submit" class="btn btn-danger" value="delete-invisible"><i class="fas fa-trash-alt"></i> {__('deleteSelected')}</button>
                            </div>
                        </div>
                    </form>
                {/if}
                {if !is_array($oBoxenContainer) || $oBoxenContainer|@count == 0}
                    <div class="alert alert-danger">{__('noTemplateConfig')}</div>
                {elseif !$oBoxenContainer.left && !$oBoxenContainer.right && !$oBoxenContainer.top && !$oBoxenContainer.bottom}
                    <div class="alert alert-danger">{__('noBoxActivated')}</div>
                {else}
                    {if isset($oEditBox) && $oEditBox}
                        <div id="editor" class="editor">
                            <form action="boxen.php" method="post">
                                {$jtl_token}
                                <div class="card editorInner">
                                    <div class="card-header">
                                        <div class="subheading1">{__('boxEdit')}</div>
                                        <hr class="mb-n3">
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group form-row align-items-center">
                                            <label class="col col-sm-4 col-form-label text-sm-right"for="boxtitle">{__('boxTitle')}:</label>
                                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                <input class="form-control" id="boxtitle" type="text" name="boxtitle" value="{$oEditBox->cTitel}" />
                                            </div>
                                        </div>
                                        {if $oEditBox->eTyp === 'text'}
                                            {foreach $availableLanguages as $language}
                                                <div class="form-group form-row align-items-center">
                                                    <label class="col col-sm-4 col-form-label text-sm-right"for="title-{$language->getIso()}">{__('boxTitle')} {$language->getLocalizedName()}</label>
                                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                        <input class="form-control" id="title-{$language->getIso()}" type="text" name="title[{$language->getIso()}]" value="{foreach $oEditBox->oSprache_arr  as $oBoxSprache}{if $language->getIso() === $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}" />
                                                    </div>
                                                </div>
                                                <textarea id="text-{$language->getIso()}" name="text[{$language->getIso()}]" class="form-control ckeditor" rows="15" cols="60">
                                                    {foreach $oEditBox->oSprache_arr as $oBoxSprache}{if $language->getIso() === $oBoxSprache->cISO}{$oBoxSprache->cInhalt}{/if}{/foreach}
                                                </textarea>
                                                <hr>
                                            {/foreach}
                                        {elseif $oEditBox->eTyp === 'catbox'}
                                            <div class="form-group form-row align-items-center">
                                                <label class="col col-sm-4 col-form-label text-sm-right"for="linkID">{__('catBoxNum')}:</label>
                                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                    <input class="form-control" id="linkID" type="text" name="linkID" value="{$oEditBox->kCustomID}">
                                                </div>
                                                <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                                    {getHelpDesc cDesc=__('catBoxNumTooltip')}
                                                </div>
                                            </div>
                                            {foreach $availableLanguages as $language}
                                                <div class="form-group form-row align-items-center">
                                                    <label class="col col-sm-4 col-form-label text-sm-right"for="title-{$language->getIso()}">{__('boxTitle')} {$language->getLocalizedName()}:</label>
                                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                        <input class="form-control" id="title-{$language->getIso()}" type="text"
                                                               name="title[{$language->getIso()}]"
                                                               value="{foreach $oEditBox->oSprache_arr as $oBoxSprache}{if $language->getIso() === $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}">
                                                    </div>
                                                </div>
                                            {/foreach}
                                        {elseif $oEditBox->eTyp === 'link'}
                                            <div class="form-group form-row align-items-center">
                                                <label class="col col-sm-4 col-form-label text-sm-right"for="linkID">{__('linkgroup')}:</label>
                                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                    <select class="custom-select" id="linkID" name="linkID" required>
                                                        <option value="" {if $oEditBox->kCustomID == 0}selected="selected"{/if}>{__('FillOut')}</option>
                                                        {foreach $oLink_arr as $link}
                                                            <option value="{$link->getID()}" {if $link->getID() == $oEditBox->kCustomID}selected="selected"{/if}>
                                                                {$link->getName()}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                            {foreach $availableLanguages as $language}
                                                <div class="form-group form-row align-items-center">
                                                    <label class="col col-sm-4 col-form-label text-sm-right"for="title-{$language->getIso()}">{__('boxTitle')} ({$language->getLocalizedName()}):</label>
                                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                        <input class="form-control" id="title-{$language->getIso()}" type="text" name="title[{$language->getIso()}]" value="{foreach $oEditBox->oSprache_arr as $oBoxSprache}{if $language->getIso() === $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}" />
                                                    </div>
                                                </div>
                                            {/foreach}
                                        {/if}
                                        <input type="hidden" name="item" id="editor_id" value="{$oEditBox->kBox}" />
                                        <input type="hidden" name="action" value="edit" />
                                        <input type="hidden" name="typ" value="{$oEditBox->eTyp}" />
                                        <input type="hidden" name="page" value="{$nPage}" />
                                        {if !empty($oEditBox->kBox) && $oEditBox->supportsRevisions === true}
                                            {getRevisions type='box' key=$oEditBox->kBox show=['cTitel', 'cInhalt'] secondary=true data=$revisionData}
                                        {/if}
                                    </div>
                                    <div class="card-footer save-wrapper">
                                        <div class="row">
                                            <div class="ml-auto col-sm-6 col-xl-auto">
                                                <button type="button" onclick="window.location.href='boxen.php'" class="btn btn-outline-primary btn-block">
                                                    {__('cancelWithIcon')}
                                                </button>
                                            </div>
                                            <div class="col-sm-6 col-xl-auto">
                                                <button type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                                    {__('saveWithIcon')}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    {else}
                        {if $nPage === 0}
                            <div class="alert alert-info">{__('warningChangesForAllPages')}</div>
                        {/if}
                        <div class="card">
                            <div class="card-body">
                                <form name="boxen" method="post" action="boxen.php">
                                    {$jtl_token}
                                    <div class="input-group left">
                                        <span class="input-group-addon">
                                            <label for="{__('page')}">{__('page')}:</label>
                                        </span>
                                        <span class="label-wrap last">
                                            <select name="page" class="selectBox custom-select" id="{__('page')}" onchange="document.boxen.submit();">
                                                {include file='tpl_inc/seiten_liste.tpl'}
                                            </select>
                                        </span>
                                        <input type="hidden" name="boxen" value="1" />
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="boxWrapper row">
                            {include file='tpl_inc/boxen_side.tpl'}
                            {include file='tpl_inc/boxen_middle.tpl'}
                        </div>
                    {/if}
                {/if}
            </div>
            <div id="config" class="tab-pane fade{if $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl'
                    config=$oConfig_arr
                    name='einstellen'
                    a='saveSettings'
                    action='boxen.php'
                    buttonCaption=__('save')
                    title=__('settings')
                    tab='einstellungen'
                    showNonConf=true}
            </div>
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}
