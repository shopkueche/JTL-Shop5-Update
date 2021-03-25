<div class="tab-pane fade" id="upload">
    <form enctype="multipart/form-data">
        {$jtl_token}
        <div class="form-group">
            {include file='tpl_inc/fileupload.tpl'
            fileID='plugin-install-upload'
            fileUploadUrl="{$adminURL}/pluginverwaltung.php"
            fileBrowseClear=true
            fileUploadAsync=true
            fileAllowedExtensions="['zip']"
            fileMaxSize=100000
            fileOverwriteInitial=false
            fileShowUpload=true
            fileShowRemove=true
            fileDefaultBatchSelectedEvent=false
            fileSuccessMsg="{__('successPluginUpload')}"
            }
        </div>
        <hr>
    </form>

    <script>
        let defaultError = '{__('errorPluginUpload')}',
            $fi          = $('#plugin-install-upload');
        {literal}
        $fi.on('fileuploaded', function(event, data, previewId, index) {
            var response = data.response,
                alert = $('#plugin-install-upload-upload-error');
            if (response.status === 'OK') {
                alert.hide();
                var wasActiveVerfuegbar = $('#verfuegbar').hasClass('active'),
                    wasActiveFehlerhaft = $('#fehlerhaft').hasClass('active');
                $('#verfuegbar').replaceWith(response.html.available);
                $('#fehlerhaft').replaceWith(response.html.erroneous);
                $('a[href="#fehlerhaft"]').find('.badge').html(response.html.erroneous_count);
                $('a[href="#verfuegbar"]').find('.badge').html(response.html.available_count);
                $('#plugin-install-upload-upload-success').show().removeClass('hidden');
                if (wasActiveFehlerhaft) {
                    $('#fehlerhaft').addClass('active show');
                } else if (wasActiveVerfuegbar) {
                    $('#verfuegbar').addClass('active show');
                }
            } else {
                if (response.errorMessage !== null && response.errorMessage.length > 0) {
                    alert.html(defaultError + ': ' + response.errorMessage);
                } else {
                    alert.html(defaultError);
                }
                alert.show().removeClass('hidden');
            }
            $fi.fileinput('reset');
            $fi.fileinput('clear');
            $fi.fileinput('refresh');
            $fi.fileinput('enable');
        });
        {/literal}
    </script>
</div>
