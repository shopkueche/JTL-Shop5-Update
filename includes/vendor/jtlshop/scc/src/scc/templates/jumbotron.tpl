<{$params.tag}
    class="jumbotron{if $params.class->hasValue()} {$params.class->getValue()}{/if}
    {if $params['text-variant']->hasValue()} text-{$params['text-variant']->getValue()}{/if}
    {if $params['bg-variant']->hasValue()} bg-{$params['bg-variant']->getValue()}{/if}
    {if $params['border-variant']->hasValue()} border border-{$params['border-variant']->getValue()}{/if}
"
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
{if $params.header->hasValue()}
    <{$params['header-tag']} class="display-4">
        {$params.header->getValue()}
    </{$params['header-tag']}>
{/if}
{if $params.lead->hasValue()}
    <{$params['lead-tag']} class="lead">
    {$params.lead->getValue()}
    </{$params['lead-tag']}>
{/if}
{$blockContent}
</{$params.tag}>
