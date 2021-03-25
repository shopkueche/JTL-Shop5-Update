{*
    Display a CSV import button for a CSV importer with the unique $importerId

    @param string importerId - the id string for this CSV importer
    @param bool bCustomStrategy - Show modal dialog to choose the import strategy (default: false)
*}
{assign var=bCustomStrategy value=$bCustomStrategy|default:true}
{$importerType=$importerType|default:$importerId}
<script>
    var $form_{$importerId} = null;
    var $fileInput_{$importerId} = null;

    $(function ()
    {
        var $importcsvInput = $('<input>', { type: 'hidden', name: 'importcsv', value: '{$importerType}' });
        var $tokenInput     = $('{$jtl_token}');

        $fileInput_{$importerId} = $('<input>', { type: 'file', name: 'csvfile', accept: '.csv,.slf' });
        $fileInput_{$importerId}.hide();
        $fileInput_{$importerId}.on('change', function () {
            {if $bCustomStrategy === true}
                $('#modal-{$importerId}').modal('show');
            {else}
                $form_{$importerId}.submit();
            {/if}
        });

        $form_{$importerId} = $(
            '<form>',
            {
                method: 'post', enctype: 'multipart/form-data',
                action: window.location.pathname
            }
        );
        $form_{$importerId}.append($importcsvInput, $fileInput_{$importerId}, $tokenInput);

        $('body').append($form_{$importerId});
    });

    function onClickCsvImport_{$importerId} ()
    {
        $fileInput_{$importerId}.click();
    }

    {if $bCustomStrategy === true}
        function onModalCancel_{$importerId} ()
        {
            $('#modal-{$importerId}').modal('hide');
        }

        function onModalSubmit_{$importerId} ()
        {
            $('#modal-{$importerId}').modal('hide');
            $form_{$importerId}
                .append($('#importType-{$importerId}'))
                .submit();
        }
    {/if}

    $(window).on('load', function () {
        $('#modal-{$importerId}').detach().appendTo("body");
    })
</script>
{if $bCustomStrategy === true}
    <div class="modal" tabindex="-1" role="dialog" id="modal-{$importerId}">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{__('importCsvChooseType')}</h2>
                    <button type="button" class="close" data-dismiss="modal">
                        <i class="fal fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="importType-{$importerId}" class="sr-only">{__('importCsvChooseType')}</label>
                    <select class="custom-select" name="importType" id="importType-{$importerId}">
                        <option value="0">{__('importCsvType0')}</option>
                        <option value="1">{__('importCsvType1')}</option>
                        <option value="2">{__('importCsvType2')}</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button type="button" class="btn btn-outline-primary btn-block" onclick="onModalCancel_{$importerId}();">
                                {__('cancelWithIcon')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button type="button" class="btn btn-primary btn-block" onclick="onModalSubmit_{$importerId}();">
                                <i class="fa fa-upload"></i> {__('importCsv')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}
<button type="button" class="btn btn-outline-primary btn-block" onclick="onClickCsvImport_{$importerId}()">
    <i class="fal fa-upload"></i> {__('importCsv')}
</button>
