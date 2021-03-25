{*
    Parameters:
        modalID - unique id for the modal (e.g. 'reset-payment')
        modalTitle - the modal dialogs title
        modalBody (optional) - body text of the modal with more information
*}
<div id="{$modalID}-modal" class="modal" tabindex="-1" role="dialog">
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
                        <button id="{$modalID}-confirm" type="button" class="btn btn-primary">
                            <i class="fal fa-check text-success"></i> {__('confirm')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('#{$modalID}-confirm').on('click', function(){
            var $modalButton = $('button[data-target="#{$modalID}-modal"');

            if($modalButton.data('href')) {
                window.location.href = $modalButton.data('href');
            } else if ($modalButton.data('form')) {
                $($modalButton.data('form')).submit();
            }
        });
    });
</script>
