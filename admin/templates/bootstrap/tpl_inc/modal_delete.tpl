{*
    Parameters:
        modalID - unique id for the modal (e.g. 'reset-payment')
        modalTitle - the modal dialogs title
        modalBody (optional) - body text of the modal with more information
        triggerName  - the hidden input data-name to trigger the form post submit
*}
<script>
    function submitDeletion() {ldelim}
    $('input[data-id="{$triggerName}"]').val(1).trigger('click');
    {rdelim}
</script>
<div class="modal delete-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{$modalTitle}</h2>
            </div>
            <div class="modal-body">{if isset($modalBody)}{$modalBody}{/if}</div>
            <div class="modal-footer">
                <p>{__('wantToConfirm')}</p>
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">
                            {__('cancelWithIcon')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="button" id="{$modalID}-delete" onclick="submitDeletion()" data-name="" class="btn btn-danger">{__('delete')}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>