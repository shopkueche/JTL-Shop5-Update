{if $params.variant->hasValue()}
    {assign var=class value='btn '|cat:$params.class|cat:' btn-'|cat:$params.variant}
{else}
    {assign var=class value='btn '|cat:$params.class}
{/if}

{if $params.href->hasValue()}
    {$tag = 'a'}
{else}
    {$tag = 'button'}
{/if}

<{$tag}
    {if $tag == 'button'}type="{$params.type}"{/if}
    class="{$class}{if $params.size->hasValue()} btn-{$params.size->getValue()}{/if}{if $params.block->getValue() === true} btn-block{/if}"
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.href->hasValue()}href="{$params.href->getValue()}"{/if}
    {if $params.target->hasValue()}target="{$params.target->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.title->hasValue()}title="{$params.title->getValue()}" {/if}
    {if $params.name->hasValue()}name="{$params.name->getValue()}" {/if}
    {if $params.value->hasValue()}value="{$params.value->getValue()}" {/if}
    {if $params.disabled->getValue() === true}disabled{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
    {if $params.type === 'submit' && $params.formnovalidate->getValue() === true}formnovalidate{/if}>
    {$blockContent}
</{$tag}>
