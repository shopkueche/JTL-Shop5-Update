<div id="modalSelect" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>{__('plz_ort_import_select')}</h2>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                {if isset($oLand_arr) && count($oLand_arr) > 0}
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{__('iso')}</th>
                                    <th>{__('country')}</th>
                                    <th>{__('date')}</th>
                                    <th>{__('size')}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $oLand_arr as $oLand}
                                <tr>
                                    <td>{$oLand->cISO}</td>
                                    <td>{$oLand->cDeutsch}</td>
                                    <td>{$oLand->cDate}</td>
                                    <td>{$oLand->cSize}</td>
                                    <td><a href="#" data-callback="plz_ort_import" data-ref="{$oLand->cURL}"><i class="fa fa-download"></i></a></td>
                                </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                <div class="alert alert-warning"><i class="fal fa-exclamation-triangle"></i> {__('plz_ort_import_select_failed')}</div>
                {/if}
            </div>
            <div class="modal-footer">
                <div class="row mt-3">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a href="#" class="btn btn-outline-primary btn-block" data-dismiss="modal">
                            {__('cancelWithIcon')}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>