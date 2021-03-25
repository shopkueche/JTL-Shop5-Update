{if $account}
        <button class="navbar-toggler sidebar-toggler collapsed" type="button" data-toggle="collapse" data-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        </div>
    </div>
</div>{* /backend-wrapper *}

<script>
    if(typeof CKEDITOR !== 'undefined') {
        CKEDITOR.editorConfig = function(config) {
            config.language = '{$language}';
            config.removeDialogTabs = 'link:upload;image:Upload';
            config.defaultLanguage = 'en';
            config.startupMode = '{if isset($Einstellungen.global.admin_ckeditor_mode)
                && $Einstellungen.global.admin_ckeditor_mode === 'Q'}source{else}wysiwyg{/if}';
            config.htmlEncodeOutput = false;
            config.basicEntities = false;
            config.htmlEncodeOutput = false;
            config.allowedContent = true;
            config.enterMode = CKEDITOR.ENTER_P;
            config.entities = false;
            config.entities_latin = false;
            config.entities_greek = false;
            config.ignoreEmptyParagraph = false;
            config.filebrowserBrowseUrl      = 'elfinder.php?ckeditor=1&mediafilesType=misc&token={$smarty.session.jtl_token}';
            config.filebrowserImageBrowseUrl = 'elfinder.php?ckeditor=1&mediafilesType=image&token={$smarty.session.jtl_token}';
            config.filebrowserFlashBrowseUrl = 'elfinder.php?ckeditor=1&mediafilesType=video&token={$smarty.session.jtl_token}';
            config.filebrowserUploadUrl      = 'elfinder.php?ckeditor=1&mediafilesType=misc&token={$smarty.session.jtl_token}';
            config.filebrowserImageUploadUrl = 'elfinder.php?ckeditor=1&mediafilesType=image&token={$smarty.session.jtl_token}';
            config.filebrowserFlashUploadUrl = 'elfinder.php?ckeditor=1&mediafilesType=video&token={$smarty.session.jtl_token}';
            config.extraPlugins = 'codemirror';
            config.fillEmptyBlocks = false;
            config.autoParagraph = false;
        };
        CKEDITOR.editorConfig(CKEDITOR.config);
        $.each(CKEDITOR.dtd.$removeEmpty, function (i, value) {
            CKEDITOR.dtd.$removeEmpty[i] = false;
        });
    }
    $('.select2').select2();
    $(function() {
        ioCall('notificationAction', ['update'], undefined, undefined, undefined, true);
    });
</script>

{/if}
</body></html>
