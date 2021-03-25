<div id="uninstall-{$context}-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{__('deletePluginData')}</h2>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <label for="delete-files-{$context}">{__('deletePluginFilesQuestion')}</label>
                <input type="checkbox" id="delete-files-{$context}" name="delete-files">
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto submit">
                        <button type="button" class="delete-plugindata-yes btn btn-danger btn-bock">
                            <i class="fa fa-close"></i>&nbsp;{__('deletePluginDataYes')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto submit">
                        <button type="button" class="delete-plugindata-no btn btn-outline-primary">
                            <i class="fa fa-close"></i>&nbsp;{__('deletePluginDataNo')}
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
            data += '&deinstallieren=1&delete-data=';
            if (deleteData === true) {
                data += '1';
            } else {
                data += '0';
            }
            data += '&delete-files=';
            if (document.getElementById('delete-files-{$context}').checked) {
                data += '1'
            } else {
                data += '0'
            }
            simpleAjaxCall('pluginverwaltung.php', data, function () {
                location.reload();
            });
            return false;
        }
    });
</script>
