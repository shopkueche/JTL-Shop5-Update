{if $params.variant->hasValue()}
    {assign var=class value=$params.class->getValue()|cat:' badge-'|cat:$params.variant}
{else}
    {assign var=class value=$params.class->getValue()}
{/if}
<{$params.tag}
    class="badge {$class}{if $params.active->getValue() === true} active{/if}{if $params.disabled->getValue() === true} disabled{/if}{if $params.pill->getValue() === true} badge-pill{/if}"
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}>
{$blockContent}
</{$params.tag}>
