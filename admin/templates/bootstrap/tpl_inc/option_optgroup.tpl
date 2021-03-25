<select class="custom-select" name="{$setting->elementID}" id="{$setting->elementID}">
    {foreach $setting->optGroups as $optgroup}
        <optgroup label="{__($optgroup->name)}">
            {foreach $optgroup->values as $option}
                <option value="{$option->value}" {if $option->value === $setting->value}selected="selected"{/if}>
                    {__($option->name)}
                </option>
            {/foreach}
        </optgroup>
    {/foreach}
</select>
