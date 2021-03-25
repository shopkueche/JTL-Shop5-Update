{assign var=rounded value=''}

{if $params.rounded->getValue() !== false}
    {if $params.rounded->getValue() === true}
        {assign var=rounded value='img-rounded'}
    {elseif $params.rounded->getValue() === 'circle'}
        {assign var=rounded value='img-circle'}
    {/if}
{/if}

<img
    src="{$params.src->getValue()}"
    {if $params.srcset->hasValue()}srcset="{$params.src->getValue()}"{/if}
    {if $params.sizes->hasValue()}sizes="{$params.sizes->getValue()}"{/if}
    class="{$params.class->getValue()} {$rounded}
        {if $params.fluid->getValue() === true} img-responsive{/if}
        {if $params['fluid-grow']->getValue() === true} img-responsive w-100{/if}
        {if $params.thumbnail->getValue() === true} img-thumbnail{/if}
        {if $params.left->getValue() === true} float-left{/if}
        {if $params.right->getValue() === true} float-right{/if}
        {if $params.center->getValue() === true} mx-auto d-block{/if}
        {if $params.lazy->getValue() === true} lazy{/if}"
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.title->hasValue()}title="{$params.title->getValue()}"{/if}
    {if $params.alt->hasValue()}alt="{$params.alt->getValue()}"{/if}
    {if $params.width->hasValue()}width="{$params.width->getValue()}"{/if}
    {if $params.height->hasValue()}height="{$params.height->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.aria->hasValue()}
        {foreach $params.aria->getValue() as $ariaKey => $ariaVal} aria-{$ariaKey}="{$ariaVal}" {/foreach}
    {/if}
    {if $params.data->hasValue()}
        {foreach $params.data->getValue() as $dataKey => $dataVal} data-{$dataKey}="{$dataVal}" {/foreach}
    {/if}
>