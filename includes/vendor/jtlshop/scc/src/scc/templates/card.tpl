<div class="card {$params.class->getValue()}
    {if $params['text-variant']->hasValue()}text-{$params['text-variant']->getValue()}{/if}
    {if $params['border-variant']->hasValue()}border-{$params['border-variant']->getValue()}{/if}
    {if $params['bg-variant']->hasValue()}bg-{$params['bg-variant']->getValue()}{/if}"
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope{/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
    {if $params.aria->hasValue()}
        {foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}
    {/if}
    {if $params.data->hasValue()}
        {foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}
    {/if}
>
    {if $params['img-src']->hasValue()}
        <img class="card-img-top" src="{$params['img-src']}" alt="{$params['img-alt']|default:''}">
    {/if}
    {if $params.header->hasValue()}
        <{$params['header-tag']} class="card-header
            {if $params['header-bg-variant']->hasValue()}bg-{$params['header-bg-variant']->getValue()}{/if}
            {if $params['header-text-variant']->hasValue()}text-{$params['header-text-variant']->getValue()}{/if}
            {if $params['header-border-variant']->hasValue()}border-{$params['header-border-variant']->getValue()}{/if}"
        >
            {$params.header}
        </{$params['header-tag']}>
    {/if}
    {if $params['no-body']->getValue() === false}
        <div class="card-body{if $params.overlay->getValue() === true} card-img-overlay{/if}">
    {/if}
    {if $params['title-text']->hasValue()}
        <{$params['title-tag']->getValue()} class="card-title">{$params['title-text']}</{$params['title-tag']->getValue()}>
    {/if}
    {if $params.subtitle->hasValue()}
        <{$params['subtitle-tag']->getValue()} class="card-subtitle mb-2 text-muted">
            {$params.subtitle}
        </{$params['subtitle-tag']->getValue()}>
    {/if}
    {$blockContent|default:''}
    {if $params['no-body']->getValue() === false}
        </div>
    {/if}
    {if $params.footer->hasValue()}
        <{$params['footer-tag']} class="card-footer
            {if $params['footer-bg-variant']->hasValue()}bg-{$params['footer-bg-variant']->getValue()}{/if}
            {if $params['footer-text-variant']->hasValue()}text-{$params['footer-bg-variant']->getValue()}{/if}
            {if $params['footer-border-variant']->hasValue()}border-{$params['footer-border-variant']->getValue()}{/if}"
        >
            {$params.footer}
        </{$params['footer-tag']}>
    {/if}
</div>
