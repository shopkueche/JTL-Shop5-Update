<select class="custom-select" name="{$setting->elementID}" id="{$setting->elementID}">
    {foreach $setting->options as $option}
        <option value="{$option->value}" {if $option->value == $setting->value}selected="selected"{/if}>{__($option->name)}</option>
    {/foreach}
</select>
