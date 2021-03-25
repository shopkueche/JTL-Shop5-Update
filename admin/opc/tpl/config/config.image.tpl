<div class="form-group">
    <label for="config-{$propname}">
        {if !empty($propdesc.label)}{$propdesc.label}{/if}
    </label>

    <input type="hidden" name="{$propname}" value="{$propval|escape:'html'}">
    {if empty($propval)}
        {$imgsrc = 'opc/gfx/upload-stub.png'}
    {else}
        {$imgsrc = \JTL\Shop::getURL()|cat:'/'|cat:$smarty.const.STORAGE_OPC|cat:$propval}
    {/if}
    <button type="button" class="image-btn" onclick="opc.selectImageProp('{$propname}')">
        <img src="{$imgsrc|escape:'html'}"
             alt="Chosen image" id="preview-img-{$propname}" class="{if !empty($propdesc.thumb)}thumb{/if}">
    </button>
</div>
