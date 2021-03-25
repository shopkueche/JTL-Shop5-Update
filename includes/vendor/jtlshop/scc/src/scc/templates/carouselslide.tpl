<div
    class="carousel-item {$params.class->getValue()}{if $params.active->getValue() === true} active{/if}"
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
>
    {if $params['img-src']->hasValue()}
        <img class="d-block w-100" src="{$params['img-src']->getValue()}" alt="{$params['img-alt']->getValue()}">
        {if $params.caption->hasValue()}
        <div class="carousel-caption d-none d-md-block">
            <{$params['caption-tag']->getValue()}>
                {$params.caption->getValue()}
            </{$params['caption-tag']->getValue()}>
            <{$params['caption-text-tag']->getValue()}>
                {$params['caption-text']->getValue()}
            </{$params['caption-text-tag']->getValue()}>
        </div>
        {/if}
    {/if}
</div>
{$tmp = $parentSmarty->getTemplateVars('carouselSlides')|default:0}
{assign var=tmp value=$tmp+1}
{$x = $parentSmarty->assign('carouselSlides', $tmp)}
