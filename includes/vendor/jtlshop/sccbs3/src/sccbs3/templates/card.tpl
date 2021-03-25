{assign var="context" value="default"}
{if $params['bg-variant']->hasValue()}
    {assign var="context" value=$params['bg-variant']->getValue()}
{/if}
<div class="panel panel-{$context} {$params.class->getValue()}
        {*{if $params.textVariant->hasValue()}text-{$params.textVariant->getValue()}{/if}*}
        {*{if $params.borderVariant->hasValue()}border-{$params.borderVariant->getValue()}{/if}*}
        {*{if $params['bg-variant']->hasValue()}bg-{$params['bg-variant']->getValue()}{/if}*}
    "
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope{/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.aria->hasValue()}
        {foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}"{/foreach}
    {/if}
    {if $params.data->hasValue()}
        {foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}"{/foreach}
    {/if}
>
    {if $params['img-src']->hasValue()}
        <img class="card-img-top" src="{$params['img-src']}" alt="{$params['img-alt']|default:''}">
    {/if}
    {if $params.header->hasValue()}
        <{$params['header-tag']} class="panel-heading
            {*{if $params.headerBgVariant->hasValue()}bg-{$params.headerBgVariant->getValue()}{/if}*}
            {*{if $params.headerTextVariant->hasValue()}text-{$params.headerTextVariant->getValue()}{/if}*}
            {*{if $params.headerBorderVariant->hasValue()}border-{$params.headerBorderVariant->getValue()}{/if}*}
        ">
            {$params.header}
        </{$params['header-tag']}>
    {/if}
    {if $params['no-body']->getValue() === false}
        <div class="panel-body">
    {/if}
    {if $params.title->hasValue()}
            <{$params['title-tag']->getValue()} class="card-title">
                {$params.title}
            </{$params['title-tag']->getValue()}>
    {/if}
    {if $params.subtitle->hasValue()}
            <{$params['subtitle-tag']->getValue()} class="card-subtitle mb-2 text-muted">
                {$params.subtitle}
            </{$params['subtitle-tag']->getValue()}>
    {/if}
    {$blockContent|default:''}
    {*{foreach $params.links as $link}*}
        {*<a href="#" class="card-link">{$link}</a>*}
    {*{/foreach}*}
    {if $params['no-body']->getValue() === false}
        </div>
    {/if}
    {if $params.footer->hasValue()}
        <{$params['footer-tag']} class="panel-footer
            {*{if $params.footerBgVariant->hasValue()}bg-{$params.footerBgVariant->getValue()}{/if}*}
            {*{if $params.footerTextVariant->hasValue()}text-{$params.footerBgVariant->getValue()}{/if}*}
            {*{if $params.footerBorderVariant->hasValue()}border-{$params.footerBorderVariant->getValue()}{/if}*}
        ">
            {$params.footer}
        </{$params['footer-tag']}>
    {/if}
</div>