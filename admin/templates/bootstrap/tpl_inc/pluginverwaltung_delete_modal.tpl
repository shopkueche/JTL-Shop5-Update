<div id="uninstall-{$context}-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{__('deletePluginFilesHeading')}</h2>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto submit">
                        <button type="button" class="delete-plugindata-yes btn btn-danger btn-bock">
                            <i class="fa fa-close"></i>&nbsp;{__('deletePluginDataYes')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto submit">
                        <button type="button" class="btn btn-primary" name="cancel" data-dismiss="modal">
                            <i class="fal fa-check text-success"></i>&nbsp;{__('cancel')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var disModal = $('#uninstall-{$context}-modal');
        $('{$button}').on('click', function(event) {
            disModal.modal('show');
            return false;
        });
        $('#uninstall-{$context}-modal .delete-plugindata-yes').on('click', function (event) {
            disModal.modal('hide');
            uninstall(true);
        });
        $('#uninstall-{$context}-modal .delete-plugindata-no').on('click', function (event) {
            disModal.modal('hide');
            uninstall(false);
        });
        function uninstall(deleteData) {
            var data = $('{$selector}').serialize();
            data += '&delete=1&delete-data=1&delete-files=1';
            $('{$selector} input[type=checkbox]:checked').each(function (i, ele) {
                var name = $(ele).attr('value');
                data += '&ext[' + name + ']=' + $('#plugin-ext-' + name).val();
            });
            simpleAjaxCall('pluginverwaltung.php', data, function (res) {
                location.reload();
            });
            return false;
        }
    });
</script>
