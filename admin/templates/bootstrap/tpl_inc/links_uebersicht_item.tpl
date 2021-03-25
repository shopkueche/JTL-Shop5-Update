{if !isset($kPlugin)}
    {$kPlugin = 0}
{/if}
{foreach $list as $link}
    {$missingLinkTranslations = $linkAdmin->getMissingLinkTranslations($link->getID())}
    {$isReference = $link->getReference() > 0}
    <tr class="link-item{if $kPlugin > 0 && $kPlugin == $link->getPluginID()} highlight{/if}{if $link->getLevel() == 0} main{/if}">
        {math equation="a * b" a=$link->getLevel()-1 b=20 assign=fac}
        <td style="width: 30%">
            <div style="margin-left:{if $fac > 0}{$fac}px{else}0{/if}; padding-top: 7px" {if $link->getLevel() > 0 && $link->getParent() > 0}class="sub"{/if}>
                {if $isReference === true}<i>{/if}
                {$link->getDisplayName()}
                {if $isReference === true} ({__('Referenz')})</i>{/if}
                {if $missingLinkTranslations|count > 0}
                    <i title="{__('missingTranslations')}: {$missingLinkTranslations|count}"
                       class="fal fa-exclamation-triangle text-warning"
                       data-toggle="tooltip"
                       data-placement="top"></i>
                {/if}
                {if $link->hasDuplicateSpecialLink()}
                    <i title="{sprintf(__('hasDuplicateSpecialLink'), '')}"
                       class="duplicate-special-link fal fa-exclamation-triangle text-danger"
                       data-toggle="tooltip"
                       data-placement="top"></i>
                {/if}
            </div>
        </td>
        <td class="text-center floatforms min-w-sm" style="width: 60%">
            <div class="row">
                <form class="navbar-form2 col-lg-4 col-md-12 left px-1" method="post" action="links.php"
                      name="aenderlinkgruppe_{$link->getID()}_{$id}">
                    {$jtl_token}
                    <input type="hidden" name="action" value="move-to-linkgroup" />
                    <input type="hidden" name="kLink" value="{$link->getID()}" />
                    <input type="hidden" name="kLinkgruppeAlt" value="{$id}" />
                    {if $kPlugin > 0}
                        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                    {/if}
                    {if $link->getParent() === 0}
                        <select title="{__('linkGroupMove')}" class="custom-select" name="kLinkgruppe" onchange="document.forms['aenderlinkgruppe_{$link->getID()}_{$id}'].submit();">
                            <option value="-1">{__('linkGroupMove')}</option>
                            {foreach $linkgruppen as $linkgruppeTMP}
                                {if $linkgruppeTMP->getID() != $id && $linkgruppeTMP->getID() > 0}
                                    <option value="{$linkgruppeTMP->getID()}">{$linkgruppeTMP->getName()}</option>
                                {/if}
                            {/foreach}
                        </select>
                    {else}
                        <select title="{__('linkGroupMove')}" class="custom-select" disabled>
                            <option selected>{__('linkModificationOnlyRoot')}</option>
                        </select>
                    {/if}
                </form>
                <form class="navbar-form2 col-lg-4 col-md-12 left px-1" method="post" action="links.php" name="kopiereinlinkgruppe_{$link->getID()}_{$id}">
                    {$jtl_token}
                    <input type="hidden" name="action" value="copy-to-linkgroup" />
                    <input type="hidden" name="kLink" value="{$link->getID()}" />
                    {if $kPlugin > 0}
                        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                    {/if}
                    {if $id > 0}
                        {if $link->getParent() === 0}
                            <select title="{__('linkGroupCopy')}" class="custom-select" name="kLinkgruppe" onchange="document.forms['kopiereinlinkgruppe_{$link->getID()}_{$id}'].submit();">
                                <option value="-1">{__('linkGroupCopy')}</option>
                                {foreach $linkgruppen as $linkgruppeTMP}
                                    {if $linkgruppeTMP->getID() !== $id && $linkgruppeTMP->getID() > 0}
                                        <option value="{$linkgruppeTMP->getID()}">{$linkgruppeTMP->getName()}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        {else}
                            <select title="{__('linkGroupCopy')}" class="custom-select" disabled>
                                <option>{__('linkModificationOnlyRoot')}</option>
                            </select>
                        {/if}
                    {/if}
                </form>
                <form class="navbar-form2 col-lg-4 col-md-12 left px-1" method="post" action="links.php" name="aenderlinkvater_{$link->getID()}_{$id}">
                    {$jtl_token}
                    <input type="hidden" name="action" value="change-parent" />
                    <input type="hidden" name="kLink" value="{$link->getID()}" />
                    <input type="hidden" name="kLinkgruppe" value="{$id}" />
                    {if $kPlugin > 0}
                        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                    {/if}
                    {if $id > 0}
                        <select title="{__('linkMove')}" class="custom-select" name="kVaterLink" onchange="document.forms['aenderlinkvater_{$link->getID()}_{$id}'].submit();">
                            <option value="-1">{__('linkMove')}</option>
                            <option value="0">-- Root --</option>
                            {foreach $list as $linkTMP}
                                {if $linkTMP->getID() !== $link->getID() && $linkTMP->getID() !== $link->getParent()}
                                    <option value="{$linkTMP->getID()}">{$linkTMP->getName()}</option>
                                {/if}
                            {/foreach}
                        </select>
                    {/if}
                </form>
            </div>
        </td>
        <td class="text-center" style="width: 10%;min-width: 160px;">
            <form method="post" action="links.php">
                {$jtl_token}
                {if $kPlugin > 0}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                {/if}
                <input type="hidden" name="kLinkgruppe" value="{$id}" />
                <input type="hidden" name="kLink" value="{$link->getID()}" />
                <div class="btn-group">
                    {$deleteCount = $linkGroupCountByLinkID[$link->getID()]|default:1}
                    <button name="action"
                            value="delete-link"
                            class="btn btn-link px-2{if $link->getPluginID() > 0} disabled{/if}"
                            {if $link->getPluginID() === 0} onclick="return confirmDelete();"{/if}
                            title="{if $deleteCount > 1}{{__('dangerLinkWillGetDeleted')}|sprintf:{$deleteCount}}{else}{__('delete')}{/if}"
                            data-toggle="tooltip">
                        <span class="icon-hover">
                            <span class="fal fa-trash-alt"></span>
                            <span class="fas fa-trash-alt"></span>
                        </span>
                        {if $deleteCount > 1} ({$deleteCount}){/if}
                    </button>
                    {if $id > 0}
                        <button name="action"
                                value="remove-linklfrom-linkgroup"
                                class="btn btn-link px-2"
                                title="{__('linkGroupRemove')}"
                                data-toggle="tooltip">
                            <span class="icon-hover">
                                <span class="fal fa-unlink"></span>
                                <span class="fas fa-unlink"></span>
                            </span>
                        </button>
                        <button name="action" value="edit-link" class="btn btn-link px-2" title="{__('modify')}"
                                data-toggle="tooltip">
                            <span class="icon-hover">
                                <span class="fal fa-edit"></span>
                                <span class="fas fa-edit"></span>
                            </span>
                        </button>
                    {/if}
                </div>
            </form>
        </td>
    </tr>
    {if $link->getChildLinks()->count() > 0}
        {include file='tpl_inc/links_uebersicht_item.tpl' list=$link->getChildLinks() id=$id}
    {/if}
{/foreach}
