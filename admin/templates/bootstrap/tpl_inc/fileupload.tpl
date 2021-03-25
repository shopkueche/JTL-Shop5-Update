{*
-----------------------------------------------------------------------------------
variable name                  | default | description
-----------------------------------------------------------------------------------
$fileID                        |         | input id
$fileName                      |         | input name
$fileRequired                  |         | input required
$fileClass                     |         | input class
$fileAllowedExtensions         | ----->  | default: ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'svg']
$fileUploadUrl                 | false   | url to upload file via ajax
$fileDeleteUrl                 |         | url to delete file via ajax
$filePreview                   | true    | enable previe image
$fileMaxSize                   | 6000    | max allowed size of file, set false for unlimited size
$fileIsSingle                  | true    | only allow one file to be uploaded
$fileInitialPreviewConfig      |         | array with json - config of initial preview
$fileInitialPreview            |         | array with html of the preview images
$fileUploadAsync               | false   | upload file asynchronously
$fileBrowseClear               | false   | clear file when browsing for new file
$fileShowUpload                | false   | show upload button
$fileShowRemove                | false   | show remove button
$fileShowCancel                | false   | show cancel button
$fileOverwriteInitial          | true    | override initial file
$fileDefaultBrowseEvent        | true    | set false and created a custom .on("filebrowse") event
$fileDefaultBatchSelectedEvent | true    | set false and created a custom .on("filebatchselected") event
$fileDefaultUploadSuccessEvent | true    | set false and created a custom .on("filebatchuploadsuccess") event
$fileDefaultUploadErrorEvent   | true    | set false and created a custom .on("fileuploaderror") event
$fileSuccessMsg                | false   | success message after upload
$fileErrorMsg                  | false   | error message while uploading - automatically generated
$fileExtraData                 |         | you also need to add the jtl_token: jtl_token: '{$smarty.session.jtl_token}'
-----------------------------------------------------------------------------------
*}
{$fileIDFull   = '#'|cat:$fileID}
{$fileShowUpload = "{if isset($fileShowUpload) && $fileShowUpload === true}true{else}false{/if}"}
{$fileShowRemove = "{if isset($fileShowRemove) && $fileShowRemove === true}true{else}false{/if}"}
{$fileShowCancel = "{if isset($fileShowCancel) && $fileShowCancel === true}true{else}false{/if}"}
{$fileUploadAsync = "{if isset($fileUploadAsync) && $fileUploadAsync === true}true{else}false{/if}"}
{$fileOverwriteInitial = "{if isset($fileOverwriteInitial) && $fileOverwriteInitial === false}false{else}true{/if}"}
{$filePreview = "{if isset($filePreview) && $filePreview === false}false{else}true{/if}"}
{$fileIsSingle = $fileIsSingle|default:true}
{$fileSuccessMsg = $fileSuccessMsg|default:false}
{$fileErrorMsg = $fileErrorMsg|default:false}
<input class="custom-file-input {$fileClass|default:''}"
       type="file"
       name="{if isset($fileName)}{$fileName}{else}{$fileID}{/if}"
       id="{$fileID}"
       tabindex="1"
       {if $fileRequired|default:false}required{/if}
       {if !$fileIsSingle}multiple{/if}/>

{if $fileSuccessMsg}
    <div id="{$fileID}-upload-success" class="alert alert-success d-none mt-3">
        {$fileSuccessMsg}
    </div>
{/if}
{if $fileErrorMsg}
    <div id="{$fileID}-upload-error" class="alert alert-danger d-none mt-3">{$fileErrorMsg}</div>
{/if}

<script>
    (function () {
        let $file = $('{$fileIDFull}'),
            $fileSuccess = $('{$fileIDFull}-upload-success'),
            $fileError = $('{$fileIDFull}-upload-error');

        $file.fileinput({
            {if isset($fileUploadUrl)}
            uploadUrl: '{$fileUploadUrl}',
            {/if}
            {if isset($fileDeleteUrl)}
            deleteUrl: '{$fileDeleteUrl}',
            {/if}
            autoOrientImage: false,
            showUpload: {$fileShowUpload},
            showRemove: {$fileShowRemove},
            showCancel: {$fileShowCancel},
            cancelClass: 'btn btn-outline-primary',
            uploadClass: 'btn btn-outline-primary',
            removeClass: 'btn btn-outline-primary',
            uploadAsync: {$fileUploadAsync},
            showPreview: {$filePreview},
            initialPreviewShowDelete: false,
            fileActionSettings: {
                showZoom: false,
                showRemove: false,
                showDrag: false
            },
            uploadExtraData:
            {if isset($fileExtraData)}
                {$fileExtraData}
            {else}
                { jtl_token: '{$smarty.session.jtl_token}' }
            {/if},
            allowedFileExtensions:
            {if empty($fileAllowedExtensions)}
                ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'svg', 'webp']
            {elseif is_array($fileAllowedExtensions)}
                ["{implode('","', $fileAllowedExtensions)}"]
            {else}
                {$fileAllowedExtensions}
            {/if},
            overwriteInitial: {$fileOverwriteInitial},
            {if $fileIsSingle}
            initialPreviewCount: 1,
            {/if}
            theme: 'fas',
            language: '{$language|mb_substr:0:2}',
            browseOnZoneClick: true,
            {if !isset($fileMaxSize) || $fileMaxSize}
            maxFileSize: {$fileMaxSize|default:6000},
            {/if}
            {if $fileIsSingle}
            maxFilesNum: 1,
            {/if}
            {if $filePreview !== 'false'}
            initialPreviewConfig: {if isset($fileInitialPreviewConfig)}{$fileInitialPreviewConfig}{else}[]{/if},
            initialPreview: {if isset($fileInitialPreview)}{$fileInitialPreview}{else}[]{/if},
            {/if}
        });

        {if $fileDefaultBrowseEvent|default:true}
        $file.on("filebrowse", function (event, files) {
            {if $fileBrowseClear|default:false}
                $file.fileinput('clear');
            {/if}
            $fileSuccess.addClass('d-none');
            $fileError.addClass('d-none');
        });
        {/if}
        {if $fileDefaultBatchSelectedEvent|default:true}
        $file.on("filebatchselected", function (event, files) {
            if ($file.fileinput('getFilesCount') > 0) {
                $file.fileinput("upload");
            }
        });
        {/if}
        {if $fileDefaultUploadSuccessEvent|default:true}
        $file.on('filebatchuploadsuccess', function (event, data) {
            if (data.response.status === 'OK') {
                $fileSuccess.removeClass('d-none');
            } else {
                $fileError.removeClass('d-none');
            }
        });
        {/if}
        {if $fileDefaultUploadErrorEvent|default:true}
        $file.on('fileuploaderror, fileerror', function (event, data, msg) {
            $fileError.removeClass('d-none');
            $fileError.append('<p style="margin-top:20px">' + msg + '</p>')
        });
        {/if}
        $file.on('fileuploaded', function(event, data) {
            {if $fileSuccessMsg}
                $fileSuccess.removeClass('d-none');
            {/if}
        });
    }());
</script>
