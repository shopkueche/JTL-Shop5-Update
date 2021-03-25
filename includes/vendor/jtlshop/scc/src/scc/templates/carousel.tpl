{assign var=carouselID value='carousel'|cat:uniqid()}
<div
    id="{$carouselID}"
    data-ride="carousel"
    class="carousel slide {$params.class->getValue()}"
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
    {if $params.indicators->getValue() === true && isset($carouselSlides) && $carouselSlides > 0}
        <ol class="carousel-indicators">
            {for $count = 0 to $carouselSlides - 1}
                <li data-target="#{$carouselID}" data-slide-to="{$count}" class="{if $count === 0}active{/if}"></li>
            {/for}
        </ol>
    {/if}
    <div class="carousel-inner">
        {$blockContent}
    </div>
    {if $params.controls->getValue() === true}
        <a class="carousel-control-prev" href="#{$carouselID}" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#{$carouselID}" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    {/if}
</div>
