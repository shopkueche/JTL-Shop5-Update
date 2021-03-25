<div class="radio-group">
    {foreach $setting->options as $option}
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" value="{$option->value}"
                   name="{$setting->elementID}[]"
                   id="{$setting->elementID}-{$option@index}" {if $option->value == $setting->value}checked{/if}>
            <label class="form-check-label" for="{$setting->elementID}-{$option@index}">{__($option->name)}</label>
        </div>
    {/foreach}
</div>
