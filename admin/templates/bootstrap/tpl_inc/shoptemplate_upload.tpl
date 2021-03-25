<div class="card" id="upload">
    <div class="card-header">{__('uploadTemplateHeading')}</div>
    <div class="card-body">
        <form enctype="multipart/form-data">
            {$jtl_token}
            <div class="form-group">
                {include file='tpl_inc/fileupload.tpl'
                fileID='template-install-upload'
                fileUploadUrl="{$adminURL}/shoptemplate.php"
                fileBrowseClear=true
                fileUploadAsync=true
                fileAllowedExtensions="['zip']"
                fileMaxSize=100000
                fileOverwriteInitial=false
                filePreview=false
                fileShowUpload=true
                fileShowRemove=true
                fileDefaultBatchSelectedEvent=false
                fileSuccessMsg="{__('successTemplateUpload')}"
                }
            </div>
            <hr>
        </form>
    </div>

    <script>
        let defaultError   = '{__('errorTemplateUpload')}',
            defaultSuccess = '{__('successTemplateUpload')}'
            $fi            = $('#template-install-upload');
        {literal}
        $fi.on('fileuploaded', function(event, data, previewId, index) {
            var response = data.response;
            if (response.status === 'OK' && response.html) {
                var replace = $(response.html.id);
                if (replace.length > 0) {
                    replace.html(response.html.content);
                    var succ =  $('#alert-upload-success'),
                        alert = $('#alert-upload-error');
                    alert.hide();
                    succ.html(defaultSuccess);
                    succ.show();
                }
            } else {
                var alert = $('#alert-upload-error');
                if (response.errorMessage !== null && response.errorMessage.length > 0) {
                    alert.html(response.errorMessage);
                } else {
                    alert.html(defaultError);
                }
                alert.show();
            }
            $fi.fileinput('reset');
            $fi.fileinput('clear');
            $fi.fileinput('refresh');
            $fi.fileinput('enable');
        });
        {/literal}
    </script>
</div>
