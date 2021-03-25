<div class="progress"
    {if $params.style->hasValue() || $params.height->hasValue()}style="{$params.style->getValue()}{if $params.height->hasValue()}; height: {$params.height->getValue()}{/if}" {/if}
    {if $params.id->hasValue()}id="{$params.id->getValue()}" {/if}
    {if $params.class->hasValue() || $params.height->hasValue()}class="{$params.class->getValue()}" {/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
>
    <div class="progress-bar{if $params.type->hasValue()} bg-{$params.type->getValue()}{/if}
         {if $params.striped->getValue() === true} progress-bar-striped{/if}
         {if $params.animated->getValue() === true} progress-bar-animated{/if}"
         role="{if $params.role->hasValue()}{$params.role->getValue()}{else}progressbar{/if}"
         {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
         {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
         style="width: {$params.now->getValue()}%"
         aria-valuenow="{$params.now->getValue()}"
         aria-valuemin="{$params.min->getValue()}"
         aria-valuemax="{$params.max->getValue()}">
        {if $params.title->hasValue()}{$params.title->getValue()}{/if}
    </div>
</div>