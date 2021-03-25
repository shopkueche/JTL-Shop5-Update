<{$params['header-tag']}
    {if $params.id->hasValue()} id="{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()} style="{$params.style->getValue()}"{/if}
    class="card-header {$params.class->getValue()}
    {if $params['header-bg-variant']->hasValue()} bg-{$params['header-bg-variant']->getValue()}{/if}
    {if $params['header-text-variant']->hasValue()} text-{$params['header-text-variant']->getValue()}{/if}
    {if $params['header-border-variant']->hasValue()} border-{$params['header-border-variant']->getValue()}{/if}"
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
</{$params['header-tag']}>
