{$class = 'col '|cat:$params.class->getValue()}
{if $params.offset->hasValue()}
    {$class = "{$class} col-xs-offset-{$params.offset->getValue()}"}
{elseif $params['offset-sm']->hasValue()}
    {$class = "{$class} col-xs-offset-{$params['offset-sm']->getValue()}"}
{/if}
{if $params['offset-md']->hasValue()}
    {$class = "{$class} col-sm-offset-{$params['offset-md']->getValue()}"}
{/if}
{if $params['offset-lg']->hasValue()}
    {$class = "{$class} col-md-offset-{$params['offset-lg']->getValue()}"}
{/if}
{if $params['offset-xl']->hasValue()}
    {$class = "{$class} col-lg-offset-{$params['offset-xl']->getValue()}"}
{/if}
{if $params.cols->hasValue()}
    {$class = "{$class} col-xs-{$params.cols->getValue()}"}
{/if}
{if $params.md->getValue() !== false && $params.md->getValue() !== true}
    {$class = "{$class} col-sm-{$params.md->getValue()}"}
{/if}
{if $params.lg->getValue() !== false && $params.lg->getValue() !== true}
    {$class = "{$class} col-md-{$params.lg->getValue()}"}
{/if}
{if $params.xl->getValue() !== false && $params.xl->getValue() !== true}
    {$class = "{$class} col-lg-{$params.xl->getValue()}"}
{/if}

{* These properties are not supported by bootstrap 3.X
    {if $params.order->hasValue()}
        {$class = "{$class} order-{$params.order->getValue()}"}
    {/if}
    {if $params.orderSm->hasValue()}
        {$class = "{$class} order-sm-{$params.orderSm->getValue()}"}
    {/if}
    {if $params.orderMd->hasValue()}
        {$class = "{$class} order-md-{$params.orderMd->getValue()}"}
    {/if}
    {if $params.orderLg->hasValue()}
        {$class = "{$class} order-lg-{$params.orderLg->getValue()}"}
    {/if}
    {if $params.orderXl->hasValue()}
        {$class = "{$class} order-xl-{$params.orderXl->getValue()}"}
    {/if}
    {if $params.alignSelf->hasValue()}
        {$class = "{$class} align-self-{$params.alignSelf->getValue()}"}
    {/if}
*}

<{$params.tag->getValue()}
    class="{$class}"
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
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
    {$blockContent}
</{$params.tag->getValue()}>