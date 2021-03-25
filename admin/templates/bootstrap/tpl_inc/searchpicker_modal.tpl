{*
    Parameters:
        searchPickerName - page unique id for the search picker instance (e.g. 'customer', 'product')
        modalTitle - the modal dialogs title
        searchInputLabel - the caption for the search input field
*}
<div class="modal fade" id="{$searchPickerName}-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{$modalTitle}</h2>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    <label for="{$searchPickerName}-search-input" class="sr-only">
                        {$searchInputLabel}:
                    </label>
                    <input type="text" class="form-control" id="{$searchPickerName}-search-input" placeholder="{__('search')}"
                           autocomplete="off">
                    <span class="input-group-append">
                        <button type="button" class="btn btn-default" id="{$searchPickerName}-reset-btn"
                                title="{__('deleteInput')}">
                            <i class="fa fa-eraser"></i>
                        </button>
                    </span>
                </div>
                <p id="{$searchPickerName}-list-title"></p>
                <div class="list-group" id="{$searchPickerName}-result-list" style="max-height:500px;overflow:auto;">
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-link" id="{$searchPickerName}-select-all-btn">
                        <i class="fal fa-check-square"></i>
                        {__('selectAllShown')}
                    </button>
                    <button type="button" class="btn btn-sm btn-link" id="{$searchPickerName}-unselect-all-btn">
                        <i class="fa fa-square"></i>
                        {__('unselectAllShown')}
                    </button>
                </div>
            </div>
            <div class="modal-footer text-right">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto mb-2">
                        <button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal" id="{$searchPickerName}-cancel-btn">
                            {__('cancelWithIcon')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-primary btn-block" data-dismiss="modal" id="{$searchPickerName}-apply-btn">
                            <i class="fa fa-save"></i>
                            {__('apply')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hidden" data-name="foundEntries">{__('foundEntries')}</div>
    <div class="hidden" data-name="noEntriesSelected">{__('noEntriesSelected')}</div>
    <div class="hidden" data-name="allSelectedEntries">{__('allSelectedEntries')}</div>
    <div class="hidden" data-name="searchPending">{__('searchPending')}</div>
</div>