<{$params.tag->getValue()}
    class="input-group {$params.class->getValue()}{if $params.size->hasValue()} input-group-{$params.size->getValue()}{/if}"
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    role="{if $params.role->hasValue()}{$params.role->getValue()}{else}group{/if}"
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
>
    {if $params.prepend->hasValue()}
        <div class="input-group-prepend">
            <div class="input-group-text">{$params.prepend->getValue()}</div>
        </div>
    {/if}
    {$blockContent}
    {if $params.append->hasValue()}
        {inputgroupaddon append=true is-text=true}{$params.append->getValue()}{/inputgroupaddon}
    {/if}
</{$params.tag->getValue()}>
