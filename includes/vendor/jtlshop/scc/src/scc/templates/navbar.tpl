<{$params.tag->getValue()} class="navbar {$params.class->getValue()} navbar-{$params.type->getValue()}
    {if $params.fixed->hasValue()} fixed-{$params.fixed->getValue()}{/if}
    {if $params.sticky->getValue() === true} sticky-top{/if}
    {if $params.variant->hasValue()} bg-{$params.variant->getValue()}{/if}
    {if $params.toggleable->getValue() !== false} navbar-expand-{$params.toggleable->getValue()}{/if}"
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
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
</{$params.tag->getValue()}>
