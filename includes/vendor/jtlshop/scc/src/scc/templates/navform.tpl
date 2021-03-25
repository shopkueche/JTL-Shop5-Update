{*<li class="nav-item">*}
    <form class="form-inline {$params.class->getValue()}"
        {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
        {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
        {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
        {if $params.action->hasValue()}action="{$params.action->getValue()}"{/if}
        {if $params.method->hasValue()}method="{$params.method->getValue()}"{/if}
        {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
        {if $params.itemscope->getValue() === true}itemscope {/if}
        {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
        {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
        {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
        {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
        {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
    >
        {$blockContent}
    </form>
{*</li>*}
