{block name='snippets-linkgroup-list'}
    {if isset($linkgroupIdentifier)}
    {strip}
    {assign var=checkLinkParents value=false}
        {if isset($Link) && $Link->getID() > 0}
            {assign var=activeId value=$Link->getID()}
        {elseif JTL\Shop::$kLink > 0}
            {assign var=activeId value=JTL\Shop::$kLink}
            {assign var=Link value=JTL\Shop::Container()->getLinkService()->getLinkByID($activeId)}
        {/if}
        {if !isset($activeParents) && (isset($Link))}
            {assign var=activeParents value=JTL\Shop::Container()->getLinkService()->getParentIDs($activeId)}
            {assign var=checkLinkParents value=true}
        {/if}
        {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
        {if !empty($links)}
            {block name='snippets-linkgroup-list-links'}
                {foreach $links as $li}
                    {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)}
                        {block name='snippets-linkgroup-list-links-dropdown'}
                            <li class="link-group-item nav-item nav-scrollbar-item dropdown dropdown-full{if $activeId == $li->getId()} active{/if}">
                                {link href=$li->getURL() title=$li->getName() class="nav-link dropdown-toggle" target="_self"}
                                    <span class="text-truncate">{$li->getName()}</span>
                                {/link}
                                <div class="dropdown-menu">
                                    <div class="dropdown-body">
                                        {container}
                                            {row class="lg-row-lg nav"}
                                                {col lg=4 xl=3 class="nav-item-lg-m nav-item dropdown d-lg-none"}
                                                    {block name='snippets-linkgroup-list-links-header'}
                                                        {link href=$li->getURL() title=$li->getName()}
                                                            <strong class="nav-mobile-heading">{lang key='menuShow' printf=$li->getName()}</strong>
                                                        {/link}
                                                    {/block}
                                                {/col}
                                                {foreach $li->getChildLinks() as $subli}
                                                    {col lg=4 xl=3 class='nav-item-lg-m nav-item'}
                                                        {block name='snippets-linkgroup-list-links-sublinks'}
                                                            {if !empty($subli->getName())}
                                                                {link href=$subli->getURL() rel="{if $subli->getNoFollow()}nofollow{/if}" class="submenu-headline submenu-headline-toplevel nav-link"}
                                                                    {$subli->getName()}
                                                                {/link}
                                                            {/if}
                                                        {/block}
                                                    {/col}
                                                {/foreach}
                                            {/row}
                                        {/container}
                                    </div>
                                </div>
                            </li>
                        {/block}
                    {else}
                        {block name='snippets-linkgroup-list-links-navitem'}
                            {navitem href=$li->getURL() nofollow=$li->getNoFollow() class="nav-scrollbar-item {if $activeId == $li->getId()}active{/if}" router-class="{if $tplscope=='sitemap'}nice-deco{/if}"}
                                {$li->getName()}
                            {/navitem}
                        {/block}
                    {/if}
                {/foreach}
            {/block}
        {/if}
    {/strip}
    {/if}
{/block}
