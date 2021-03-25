{*@todo: same as radiogroup.tpl*}
{assign var=id value=$params.id->getValue()|default:uniqid()}
<div class="{$params.class->getValue()}{if $params.buttons->getValue() === true} btn-group-toggle btn-group{if $params.stacked->getValue() === true}-vertical{/if} btn-group-{$params.size->getValue()}{/if}"
    id="{$id}"
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    role="{if $params.role->hasValue()}{$params.role->getValue()}{else}group{/if}"
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
>
    {$blockContent}
</div>
