<h2>{__('labelSearchCategory')}</h2>
<fieldset>
    <input class="form-control" type="text" id="categories_list_input" value="{if isset($cSearch)}{$cSearch}{/if}" autocomplete="off" />
    <div class="select_wrapper">
        <div class="search">
            <h2>{__('found')} {__('categories')}</h2>
            <select class="custom-select" multiple="multiple" name="categories_list_found">
            </select>
        </div>
        <div class="added">
            <h2>{__('selected')} {__('categories')}</h2>
            <select class="custom-select" multiple="multiple" name="categories_list_selected">
            </select>
        </div>
        <div class="clear"></div>
    </div>
    <div class="text-center btn-group">
        <a href="#" class="btn btn-outline-primary" id="categories_list_cancel">{__('cancelWithIcon')}</a>
        <a href="#" class="btn btn-primary" id="categories_list_save">{__('saveWithIcon')}</a>
    </div>
</fieldset>