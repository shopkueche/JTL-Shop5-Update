<{$params['header-tag']}
    {if $params.id->hasValue()} id="{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()} style="{$params.style->getValue()}"{/if}
    class="panel-heading {$params.class->getValue()}
        {*{if $params.headerBgVariant->hasValue()} bg-{$params.headerBgVariant->getValue()}{/if}*}
        {*{if $params.headerTextVariant->hasValue()} text-{$params.headerTextVariant->getValue()}{/if}*}
        {*{if $params.headerBorderVariant->hasValue()} border-{$params.headerBorderVariant->getValue()}{/if}*}
    "
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.aria->hasValue()}
        {foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}"{/foreach}
    {/if}
    {if $params.data->hasValue()}
        {foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}"{/foreach}
    {/if}
>
    {$blockContent}
</{$params['header-tag']}>