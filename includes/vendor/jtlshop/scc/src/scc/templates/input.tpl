<input
    type="{$params.type->getValue()}"
    class="form-control {$params.class->getValue()}{if $params['size-class']->hasValue()} form-control-{$params['size-class']->getValue()}{/if}"
    {if $params.placeholder->hasValue()}placeholder="{$params.placeholder->getValue()}"{/if}
    {if $params.readonly->getValue() === true} readonly{/if}
    {if $params.id->hasValue()} id="{$params.id->getValue()}"{/if}
    {if $params.required->getValue() === true} required{/if}
    {if $params.disabled->getValue() === true} disabled{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.value->hasValue()}value="{$params.value->getValue()}"{/if}
    {if $params.min->hasValue()}min="{$params.min->getValue()}"{/if}
    {if $params.max->hasValue()}max="{$params.max->getValue()}"{/if}
    {if $params.size->hasValue()}size="{$params.size->getValue()}"{/if}
    {if $params.maxlength->hasValue()}maxlength="{$params.maxlength->getValue()}"{/if}
    {if $params.step->hasValue()}step="{$params.step->getValue()}"{/if}
    {if $params.name->hasValue()}name="{$params.name->getValue()}"{/if}
    {if $params.autocomplete->hasValue()}autocomplete="{$params.autocomplete->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
>
