{$class = 'col '|cat:$params.class->getValue()}
{if $params.offset->hasValue()}
    {$class = "{$class} offset-{$params.offset->getValue()}"}
{/if}
{if $params['offset-sm']->hasValue()}
    {$class = "{$class} offset-sm-{$params['offset-sm']->getValue()}"}
{/if}
{if $params['offset-md']->hasValue()}
    {$class = "{$class} offset-md-{$params['offset-md']->getValue()}"}
{/if}
{if $params['offset-lg']->hasValue()}
    {$class = "{$class} offset-lg-{$params['offset-lg']->getValue()}"}
{/if}
{if $params['offset-xl']->hasValue()}
    {$class = "{$class} offset-xl-{$params['offset-xl']->getValue()}"}
{/if}
{if $params.sm->getValue() === true}
    {$class = "{$class} col-sm"}
{elseif $params.sm->getValue() !== false}
    {$class = "{$class} col-sm-{$params.sm->getValue()}"}
{/if}
{if $params.md->getValue() === true}
    {$class = "{$class} col-md"}
{elseif $params.md->getValue() !== false}
    {$class = "{$class} col-md-{$params.md->getValue()}"}
{/if}
{if $params.lg->getValue() === true}
    {$class = "{$class} col-lg"}
{elseif $params.lg->getValue() !== false}
    {$class = "{$class} col-lg-{$params.lg->getValue()}"}
{/if}
{if $params.xl->getValue() === true}
    {$class = "{$class} col-xl"}
{elseif $params.xl->getValue() !== false}
    {$class = "{$class} col-xl-{$params.xl->getValue()}"}
{/if}
{if $params.order->hasValue()}
    {$class = "{$class} order-{$params.order->getValue()}"}
{/if}
{if $params['order-sm']->hasValue()}
    {$class = "{$class} order-sm-{$params['order-sm']->getValue()}"}
{/if}
{if $params['order-md']->hasValue()}
    {$class = "{$class} order-md-{$params['order-md']->getValue()}"}
{/if}
{if $params['order-lg']->hasValue()}
    {$class = "{$class} order-lg-{$params['order-lg']->getValue()}"}
{/if}
{if $params['order-xl']->hasValue()}
    {$class = "{$class} order-xl-{$params['order-xl']->getValue()}"}
{/if}
{if $params.cols->hasValue()}
    {$class = "{$class} col-{$params.cols->getValue()}"}
{/if}
{if $params['align-self']->hasValue()}
    {$class = "{$class} align-self-{$params['align-self']->getValue()}"}
{/if}

<{$params.tag->getValue()}
    class="{$class}"
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
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
{$blockContent}
</{$params.tag->getValue()}>
