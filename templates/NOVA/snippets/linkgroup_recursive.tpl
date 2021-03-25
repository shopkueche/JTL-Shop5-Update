{block name='snippets-linkgroup-recursive'}
    {if isset($linkgroupIdentifier) && (!isset($i) || isset($limit) && $i < $limit)}
        {strip}
            {if !isset($i)}
                {assign var=i value=0}
            {/if}
            {if !isset($limit)}
                {assign var=limit value=3}
            {/if}
            {if !isset($activeId)}
                {assign var=activeId value=0}
                {if isset($Link) && $Link->getID() > 0}
                    {assign var=activeId value=$Link->getID()}
                {elseif JTL\Shop::$kLink > 0}
                    {assign var=activeId value=JTL\Shop::$kLink}
                    {assign var=Link value=JTL\Shop::Container()->getLinkService()->getLinkByID($activeId)}
                {/if}
            {/if}
            {if !isset($activeParents)}
                {assign var=activeParents value=JTL\Shop::Container()->getLinkService()->getParentIDs($activeId)}
            {/if}
            {if !isset($links)}
                {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
            {/if}
            {if !empty($links)}
                {block name='snippets-linkgroup-recursive-list'}
                    {foreach $links as $li}
                        {assign var=hasItems value=$li->getChildLinks()->count() > 0 && (($i+1) < $limit)}
                        {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                            {assign var=activeParent value=$activeParents[$i]}
                        {/if}
                        {if $hasItems}
                            <li class="link-group-item nav-item {if $hasItems}dropdown{/if}{if $li->getIsActive() || (isset($activeParent) && $activeParent == $li->getID())} active{/if}">
                                {block name='snippets-linkgroup-recursive-link'}
                                    <a class="nav-link dropdown-toggle" target="_self" href="{$li->getURL()}" data-toggle="collapse"
                                       data-target="#link_box_{$li->getID()}_{$i}"
                                       aria-expanded="{if $li->getIsActive() || (isset($activeParent) && $activeParent == $li->getID())}true{else}false{/if}">
                                        {$li->getName()}
                                    </a>
                                {/block}
                                {block name='snippets-linkgroup-recursive-has-items-nav'}
                                    {nav vertical=true class="collapse {if $li->getID() == $activeId
                                        || ((isset($activeParent)
                                        && isset($activeParent->kLink))
                                        && $activeParent->kLink == $li->getID())}show{/if}" id="link_box_{$li->getID()}_{$i}"
                                    }
                                    {block name='snippets-linkgroup-recursive-include-linkgroup-recursive'}
                                        {if $li->getChildLinks()->count() > 0}
                                            {include file='snippets/linkgroup_recursive.tpl' i=$i+1 links=$li->getChildLinks() limit=$limit activeId=$activeId activeParents=$activeParents}
                                        {else}
                                            {include file='snippets/linkgroup_recursive.tpl' i=$i+1 links=array($li) limit=$limit activeId=$activeId activeParents=$activeParents}
                                        {/if}
                                    {/block}
                                    {/nav}
                                {/block}
                            </li>
                        {else}
                            {block name='snippets-linkgroup-recursive-has-not-items'}
                                {navitem class="{if $li->getIsActive() || (isset($activeParent) && $activeParent == $li->getID())} active{/if}"
                                    href=$li->getURL()
                                }
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
