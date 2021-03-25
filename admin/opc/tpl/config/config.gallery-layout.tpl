{$options = ['grid', 'alternate', 'columns']}

{if empty($propval)}
    {$propval = 'grid'}
{/if}

<label>{$propdesc.label}</label>

<div class="conf-gallery-layout">
    {foreach $options as $option}
        <label>
            <input type="radio" name="{$propname}" value="{$option}" {if $propval === $option}checked{/if}
                   class="gallery-layout-option-{$option}">
            <span>{__($option)}</span>
        </label>
    {/foreach}
</div>