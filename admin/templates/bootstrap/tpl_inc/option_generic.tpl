<input class="form-control" type="{$setting->cType}" name="{$setting->elementID}"
    id="{$setting->elementID}"
    value="{$setting->value|escape:'html'}"
    placeholder="{__($setting->cPlaceholder)}"
    {if $setting->cType === 'checkbox' && $setting->value === '1'} checked{/if}
    {if isset($setting->rawAttributes['step'])} step="{$setting->rawAttributes['step']}"{/if}
    {if isset($setting->rawAttributes['min'])} min="{$setting->rawAttributes['min']}"{/if}
    {if isset($setting->rawAttributes['max'])} max="{$setting->rawAttributes['max']}"{/if}
    {if isset($setting->rawAttributes['maxlength'])} maxlength="{$setting->rawAttributes['maxlength']}"{/if}
    {if isset($setting->rawAttributes['pattern'])} pattern="{$setting->rawAttributes['pattern']}"{/if}
    {if isset($setting->rawAttributes['step'])} step="{$setting->rawAttributes['step']}"{/if}
    {if isset($setting->rawAttributes['size'])} size="{$setting->rawAttributes['size']}"{/if}
    {if isset($setting->rawAttributes['required'])} required="{$setting->rawAttributes['required']}"{/if}
/>
