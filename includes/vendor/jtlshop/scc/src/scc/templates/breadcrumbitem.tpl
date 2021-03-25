<{$params.tag->getValue()}
	{if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
	{if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
	class="breadcrumb-item{if $params.class->hasValue()} {$params.class->getValue()}{/if}"
	{if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
	{if $params.itemscope->getValue() === true}itemscope {/if}
	{if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
	{if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
	{if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
	{if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
	{if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
	>
	<{$params['router-tag']->getValue()} class="breadcrumb-link
	{if $params.active->getValue() === true} {$params['active-class']->getValue()}{/if}
	{if $params.disabled->getValue() === true} disabled{/if}"
	{if $params.nofollow->getValue() === true} rel="nofollow"{/if}
	{if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
	target="{$params.target->getValue()}"
	{if $params.href->hasValue()}href="{$params.href->getValue()}"{/if}
	{if $params['router-tag-itemprop']->hasValue()}itemprop="{$params['router-tag-itemprop']->getValue()}"{/if}
>
{$blockContent}
</{$params['router-tag']->getValue()}>
</{$params.tag->getValue()}>
