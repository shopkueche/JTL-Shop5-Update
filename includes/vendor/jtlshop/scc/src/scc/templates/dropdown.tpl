<div class="dropdown {if $params.dropup->getValue() === true} dropup{/if} {$params.class->getValue()}"
    {if $params.style->hasValue()} style="{$params.style->getValue()}"{/if}
    {if $params.id->hasValue()} id="{$params.id->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
>
    <button
        class="btn btn-{$params.variant->getValue()}{if $params.size->hasValue()} btn-{$params.size->getValue()}{/if} dropdown-toggle {$params['toggle-class']->getValue()}"
        type="button"
        data-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false"
        {if $params.id->hasValue()} aria-labelledby="{$params.id->getValue()}"{/if}
        {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
        {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach} {/if}
        {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach} {/if}>
        {$params.text->getValue()}</button>
    <div class="dropdown-menu{if $params.right->getValue() === true} dropdown-menu-right{/if}">
        {$blockContent}
    </div>
</div>
