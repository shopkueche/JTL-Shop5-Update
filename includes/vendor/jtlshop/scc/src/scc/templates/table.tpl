{$classes = 'table '|cat:$params.class->getValue()}
{if $params.striped->getValue() === true}
    {$classes = $classes|cat:' table-striped'}
{/if}
{if $params.hover->getValue() === true}
    {$classes = $classes|cat:' table-hover'}
{/if}
{if $params.dark->getValue() === true}
    {$classes = $classes|cat:' table-dark'}
{/if}
{if $params.bordered->getValue() === true}
    {$classes = $classes|cat:' table-bordered'}
{/if}
{if $params.borderless->getValue() === true}
    {$classes = $classes|cat:' table-borderless'}
{/if}
{if $params.outlined->getValue() === true}
    {$classes = $classes|cat:' border'}
{/if}
{if $params.small->getValue() === true}
    {$classes = $classes|cat:' table-sm'}
{/if}
{if $params.responsive->hasValue()}
    {$classes = $classes|cat:' table-responsive'}
    {if $params.responsive->getValue() !== true}
        {$classes = $classes|cat:'-'|cat:$params.responsive->getValue()}
    {/if}
{/if}

{$addHead = false}
{$fields = $params.fields->getValue()}
{foreach $fields as $k => $v}
    {if $v|is_array} {*assoc-array with labels*}
        {$addHead = true}
        {$fieldNames = $fields|array_keys}
    {else}
        {$fieldNames = $fields}
    {/if}
    {break}
{/foreach}

<table class="{$classes}"
    {if $params.id->hasValue()}id="label-{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
>
    {if $params.caption->hasValue()}<caption>{$params.caption->getValue()}</caption>{/if}
    {if $addHead === true}
        <thead>
        <tr>
        {foreach $fields as $value}
            {if isset($value.label)}
                <th>{$value.label}</th>
            {/if}
        {/foreach}
        </tr>
        </thead>
    {/if}

    {foreach $params.items->getValue() as $v}
        <tr>
        {foreach $v|get_object_vars as $key => $value}
            {if $key|in_array:$fieldNames:true}
                {if $value|is_object && isset($fields[$key]['key'])}
                    {$idx = $fields[$key]['key']}
                    <td>{$value->$idx}</td>
                {else}
                    <td>{$value}</td>
                {/if}
            {/if}
        {/foreach}
        </tr>
    {/foreach}
</table>
