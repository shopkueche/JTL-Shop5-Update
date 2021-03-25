<tr class="{if isset($borderTop)}tr-divider-top {elseif isset($borderBottom) && $borderBottom}tr-divider-bottom{/if}">
    {assign var=isActive value=$oBox->getFilter($nPage) === true || is_array($oBox->getFilter($nPage))}
    {if $oBox->getBaseType() === $smarty.const.BOX_CONTAINER}
        <td class="{if !$isActive} inactive text-muted{/if}">
            <b>Container #{$oBox->getID()}</b>
        </td>
        <td></td>
        <td></td>
    {else}
        <td class="{if !$isActive} inactive text-muted{/if}{if $oBox->getContainerID() > 0} boxSubName{/if}">
            {$oBox->getTitle()}
        </td>
        <td class="{if !$isActive} inactive text-muted{/if}">
            {$oBox->getType()|ucfirst}
        </td>
        <td class="{if !$isActive} inactive text-muted{/if}">
            {$oBox->getName()}
        </td>
    {/if}
    <td>
        <div class="custom-control custom-checkbox d-inline-block">
            <input class="custom-control-input {if ($nPage !== 0 && is_array($oBox->getFilter($nPage))) || ($nPage === 0 && !\Functional\true($oBox->getFilter()) && !\Functional\false($oBox->getFilter()))} tristate{/if}"
                   type="checkbox"
                   name="aktiv[]"
                   id="box-id-{$oBox->getID()}"
                   data-box-id="{$oBox->getID()}"
                   {if ($nPage !== 0 && $oBox->isVisibleOnPage($nPage)) || ($nPage === 0 && \Functional\true($oBox->getFilter()))}checked="checked"{/if} value="{$oBox->getID()}">
            <label class="custom-control-label" for="box-id-{$oBox->getID()}"></label>
        </div>
        <input type="hidden" name="box[]" value="{$oBox->getID()}">
        {*prevents overwriting specific visibility when indeterminate checkbox is set on 'all pages' view*}
        {if $nPage === 0 && !\Functional\true($oBox->getFilter()) && !\Functional\false($oBox->getFilter())}
            <input type="hidden" name="ignore[]" value="{$oBox->getID()}" id="boxIgnore{$oBox->getID()}">
        {/if}
        {if $nPage === 0}
            {if \Functional\true($oBox->getFilter())}
                {__('visibleOnAllPages')}
            {elseif \Functional\false($oBox->getFilter())}
                {__('deactivatedOnAllPages')}
            {else}
                {__('visibleOnSomePages')}
            {/if}
        {else}
            <ul class="box-active-filters" id="box-active-filters-{$oBox->getID()}">
                {if $oBox->getContainerID() === 0 && is_array($oBox->getFilter($nPage))}
                    {foreach $oBox->getFilter($nPage) as $pageID}
                        <li class="selected-item"><i class="fa fa-filter"></i> {$filterMapping[$pageID]}</li>
                    {/foreach}
                {/if}
            </ul>
        {/if}
    </td>
    <td>
        <div class="input-group form-counter min-w-sm">
            <div class="input-group-prepend">
                <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                    <span class="fas fa-minus"></span>
                </button>
            </div>
            <input class="form-control text-right" type="number" name="sort[]" value="{$oBox->getSort($nPage)}"
                   autocomplete="off" id="{$oBox->getSort($nPage)}">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                    <span class="fas fa-plus"></span>
                </button>
            </div>
        </div>
    </td>
    <td class="text-center">
        <div class="btn-group">
            <a href="boxen.php?action=del&page={$nPage}&position={$position}&item={$oBox->getID()}&token={$smarty.session.jtl_token}"
               onclick="return confirmDelete('{if $oBox->getBaseType() === $smarty.const.BOX_CONTAINER}Container #{$oBox->getID()}{else}{$oBox->getTitle()}{/if}');"
               title="{__('remove')}"
               class="btn btn-link px-2"
               data-toggle="tooltip">
                <span class="icon-hover">
                    <span class="fal fa-trash-alt"></span>
                    <span class="fas fa-trash-alt"></span>
                </span>
            </a>
            <a href="boxen.php?action=edit_mode&page={$nPage}&position={$position}&item={$oBox->getID()}&token={$smarty.session.jtl_token}"
               title="{__('edit')}"
               data-toggle="tooltip"
               class="btn btn-link px-2{if empty($oBox->getType()) || ($oBox->getType() !== \JTL\Boxes\Type::TEXT && $oBox->getType() !== \JTL\Boxes\Type::LINK && $oBox->getType() !== \JTL\Boxes\Type::CATBOX)} disabled{/if}">
                <span class="icon-hover">
                    <span class="fal fa-edit"></span>
                    <span class="fas fa-edit"></span>
                </span>
            </a>
            {if $oBox->getContainerID() === 0}
                {if $nPage === $smarty.const.PAGE_ARTIKEL || $nPage === $smarty.const.PAGE_ARTIKELLISTE || $nPage === $smarty.const.PAGE_HERSTELLER || $nPage === $smarty.const.PAGE_EIGENE}
                    {if $nPage === $smarty.const.PAGE_ARTIKEL}
                        {assign var=picker value='articlePicker'}
                    {elseif $nPage === $smarty.const.PAGE_ARTIKELLISTE}
                        {assign var=picker value='categoryPicker'}
                    {elseif $nPage === $smarty.const.PAGE_HERSTELLER}
                        {assign var=picker value='manufacturerPicker'}
                    {elseif $nPage === $smarty.const.PAGE_EIGENE}
                        {assign var=picker value='pagePicker'}
                    {/if}
                    {if !is_array($oBox->getFilter($nPage)) || \Functional\true($oBox->getFilter())}
                        <input type="hidden" id="box-filter-{$oBox->getID()}" name="box-filter-{$oBox->getID()}" value="">
                    {else}
                        <input type="hidden" id="box-filter-{$oBox->getID()}" name="box-filter-{$oBox->getID()}"
                               value="{foreach $oBox->getFilter($nPage) as $pageID}{if !empty($pageID)}{$pageID}{/if}{if !$pageID@last},{/if}{/foreach}">
                    {/if}
                    <button type="button" class="btn btn-link px-2"
                            onclick="openFilterPicker({$picker}, {$oBox->getID()})" data-toggle="tooltip">
                        <span class="icon-hover">
                            <span class="fal fa-filter"></span>
                            <span class="fas fa-filter"></span>
                        </span>
                    </button>
                {/if}
            {/if}
        </div>
    </td>
</tr>
