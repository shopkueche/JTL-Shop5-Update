<div class="form-group">
    <textarea style="resize:{if isset($setting->textareaAttributes.Resizable)}{$setting->textareaAttributes.Resizable}{/if};max-width:800%;width:100%;border:none"
          name="{$setting->elementID}"
          cols="{if isset($setting->textareaAttributes.Cols)}{$setting->textareaAttributes.Cols}{/if}"
          rows="{if isset($setting->textareaAttributes.Rows)}{$setting->textareaAttributes.Rows}{/if}"
          id="{$setting->elementID}"
          placeholder="{__($setting->cPlaceholder)}"
    >{$setting->cTextAreaValue|escape:'html'}
    </textarea>
</div>
