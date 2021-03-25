{if $params.href->hasValue()}
    {assign var=tag value='a'}
{else}
    {assign var=tag value=$params.tag}
{/if}

<{$tag}
    class="list-group-item {$params.class}{if $params.active->getValue() === true} active{/if}{if $params.disabled->getValue() === true} disabled{/if}{if $params.variant->hasValue()} list-group-item-{$params.variant->getValue()}{/if}"
    {if $params.style->hasValue()} style="{$params.style->getValue()}"{/if}
    {if $params.id->hasValue()} id="{$params.id->getValue()}"{/if}
    {if $params.href->hasValue()} href="{$params.href->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
>
    {$blockContent}
</{$tag}>
