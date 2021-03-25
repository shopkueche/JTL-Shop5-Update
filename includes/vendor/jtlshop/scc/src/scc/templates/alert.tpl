<div role="alert"
    class="alert{if $params.class->hasValue()} {$params.class->getValue()}{/if}
    {if $params.variant->hasValue()} alert-{$params.variant->getValue()}{/if}
    {if $params.dismissible->getValue() === true} alert-dismissable{/if}
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
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}>
{if $params.dismissible->getValue() === true}
    <button type="button" aria-label="{$params['dismiss-label']->getValue()}" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
{/if}
    {if $params.variant->hasValue()}<div class="sr-only">x</div>{/if}
    {$blockContent}
</div>
