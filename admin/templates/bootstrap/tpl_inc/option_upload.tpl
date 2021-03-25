<input type="hidden" name="{$setting->elementID}" value="upload-{$iteration}" />
{include file='tpl_inc/fileupload.tpl'
    fileID="tpl-upload-{$iteration}"
    fileName="upload-{$iteration}"
    fileDeleteUrl="{$adminURL}/shoptemplate.php?token={$smarty.session.jtl_token}"
    fileExtraData='{id:1}'
    fileMaxSize="{if !empty($setting->rawAttributes.maxFileSize)}{$setting->rawAttributes.maxFileSize}{else}1000{/if}"
    fileAllowedExtensions="{if !empty($setting->rawAttributes.allowedFileExtensions)}{$setting->rawAttributes.allowedFileExtensions}{/if}"
    fileInitialPreview="[
    {if !empty($setting->value)}
        '<img src=\"{$shopURL}/templates/{$template->getDir()}/{$setting->rawAttributes.target}{$setting->value}?v={$smarty.now}\" class=\"file-preview-image\"/>'
    {/if}
    ]"
    fileInitialPreviewConfig="[{
        url: '{$adminURL}/shoptemplate.php',
        extra: {
                upload: '{$template->getDir()}/{$setting->rawAttributes.target}{$setting->value}',
                id:     'upload-{$iteration}',
                token:  '{$smarty.session.jtl_token}',
                cName:  '{$setting->key}'
        }
    }]"
}
