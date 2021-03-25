<{$params.tag->getValue()}
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    class="nav-item dropdown{if $params.class->hasValue()} {$params.class->getValue()}{/if}"
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
>
    <a class="nav-link nav-link-custom
        {if $params['no-caret']->getValue() === false} dropdown-toggle{/if}
        {if $params.disabled->getValue() === true} disabled{/if}"
        href="#"
        data-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false"
        role="{if $params.role->hasValue()}{$params.role->getValue()}{else}button{/if}"
        {if $params['router-aria']->hasValue()}{foreach $params['router-aria']->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
        {if $params['router-data']->hasValue()}{foreach $params['router-data']->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
    >
        {$params.text->getValue()}
    </a>
    <div class="dropdown-menu
        {if $params.right->getValue() === true} dropdown-menu-right{/if}
    ">
        {$blockContent}
    </div>
</{$params.tag->getValue()}>
