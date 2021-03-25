<h1>{__('labelSearchProduct')}</h1>
<fieldset>
    <input class="form-control" type="text" id="article_list_input" value="{if isset($cSearch)}{$cSearch}{/if}" autocomplete="off" />
    <div class="select_wrapper">
        <div class="search">
            <h2>{__('found')} {__('product')}</h2>
            <select class="custom-select" multiple="multiple" name="article_list_found">
            </select>
        </div>
        <div class="added">
            <h2>{__('selected')} {__('product')}</h2>
            <select class="custom-select" multiple="multiple" name="article_list_selected">
            </select>
        </div>
        <div class="clear"></div>
    </div>
    <div class="text-center btn-group">
        <a href="#" class="btn btn-outline-primary" id="article_list_remove"><i class="fa fa-square-o"></i> {__('delete')}</a>
        <a href="#" class="btn btn-outline-primary" id="article_list_cancel">{__('cancelWithIcon')}</a>
        <a href="#" class="btn btn-outline-primary" id="article_list_add"><i class="fal fa-check text-success-square-o"></i> {__('add')}</a>
        <a href="#" class="btn btn-primary" id="article_list_save">{__('saveWithIcon')}</a>
    </div>
</fieldset>