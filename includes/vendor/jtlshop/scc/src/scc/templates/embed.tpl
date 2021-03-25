<div class="embed-responsive embed-responsive-{$params.aspect->getValue()}">
    <{$params.type->getValue()}
        class="embed-responsive-item {$params.class->getValue()}"
        {if $params.src->hasValue()}src="{$params.src->getValue()}"{/if}
        {if $params.allowfullscreen->getValue() === true}allowfullscreen{/if}
        {if $params.loop->getValue() === true}loop{/if}
        {if $params.autoplay->getValue() === true}autoplay{/if}
        {if $params.autobuffer->getValue() === true}autobuffer{/if}
        {if $params.muted->getValue() === true}muted{/if}
        {if $params.controls->getValue() === true}controls{/if}
        {if $params.poster->hasValue()}poster="{$params.poster->getValue()}"{/if}
        {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
        {if $params.preload->hasValue()}preload="{$params.preload->getValue()}"{/if}
        {if $params.width->hasValue()}width="{$params.width->getValue()}"{/if}
        {if $params.height->hasValue()}height="{$params.height->getValue()}"{/if}
        {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
        {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
        {if $params.itemscope->getValue() === true}itemscope {/if}
        {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
        {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
        {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
        {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
        {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach} {/if}
        {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach} {/if}
    >
    {$blockContent}
    </{$params.type->getValue()}>
</div>
