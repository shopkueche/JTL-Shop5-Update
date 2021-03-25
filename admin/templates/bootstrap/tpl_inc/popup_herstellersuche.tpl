<h1>{__('labelSearchManufacturer')}</h1>
<fieldset>
    <input class="form-control" type="text" id="manufacturer_list_input" value="{if isset($cSearch)}{$cSearch}{/if}" autocomplete="off" />
    <div class="select_wrapper">
        <div class="search">
            <h2>{__('found')} {__('manufacturer')}</h2>
            <select class="custom-select" multiple="multiple" name="manufacturer_list_found">
            </select>
        </div>
        <div class="added">
            <h2>{__('selected')} {__('manufacturer')}</h2>
            <select class="custom-select" multiple="multiple" name="manufacturer_list_selected">
            </select>
        </div>
        <div class="clear"></div>
    </div>
    <div class="text-center btn-group">
        <a href="#" class="btn btn-danger" id="manufacturer_list_cancel">{__('cancelWithIcon')}</a>
        <a href="#" class="btn btn-primary" id="manufacturer_list_save">{__('saveWithIcon')}</a>
    </div>
</fieldset>