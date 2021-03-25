<form action="shoptemplate.php" method="post" enctype="multipart/form-data" id="form_settings">
    {$jtl_token}
    <div id="settings" class="settings">
        {if $template->getType() === 'admin' || ($template->getType() !== 'mobil' && $template->isResponsive())}
            <input type="hidden" name="eTyp" value="{if !empty($template->getType())}{$template->getType()}{else}standard{/if}" />
        {else}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('mobile')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    {if $template->getType() === 'mobil' && $template->isResponsive()}
                        <div class="alert alert-warning">{__('warning_responsive_mobile')}</div>
                    {/if}
                    <div class="item form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="eTyp">{__('standardTemplateMobil')}</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="custom-select" name="eTyp" id="eTyp">
                                <option value="standard" {if $template->getType() === 'standard'}selected="selected"{/if}>
                                    {__('optimizeBrowser')}
                                </option>
                                <option value="mobil" {if $template->getType() === 'mobil'}selected="selected"{/if}>
                                    {__('optimizeMobile')}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        {foreach $templateConfig as $section}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__($section->name)}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="row">
                        {foreach $section->settings as $setting}
                            {if $setting->key === 'theme_default' && isset($themePreviews) && $themePreviews !== null}
                                <div class="col-sm-8 ml-auto">
                                    <div class="item form-group form-row align-items-center" id="theme-preview-wrap" style="display: none;">
                                        <span class="input-group-addon"><strong>{__('preview')}</strong></span>
                                        <img id="theme-preview" alt="" />
                                    </div>
                                    <script type="text/javascript">
                                        var previewJSON = {$themePreviewsJSON};
                                        {literal}
                                        setPreviewImage = function () {
                                            var currentTheme = $('#theme-theme_default').val(),
                                                previewImage = $('#theme-preview'),
                                                previewImageWrap = $('#theme-preview-wrap');
                                            if (typeof previewJSON[currentTheme] !== 'undefined') {
                                                previewImage.attr('src', previewJSON[currentTheme]);
                                                previewImageWrap.show();
                                            } else {
                                                previewImageWrap.hide();
                                            }
                                        };
                                        $(document).ready(function () {
                                            setPreviewImage();
                                            $('#theme-theme_default').on('change', function () {
                                                setPreviewImage();
                                            });
                                        });
                                        {/literal}
                                    </script>
                                </div>
                            {/if}
                            <div class="col-xs-12 col-md-12">
                                <div class="item form-group form-row align-items-center">
                                    {if $setting->isEditable}
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$setting->elementID}">{__($setting->name)}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            {if $setting->cType === 'select'}
                                                {include file='tpl_inc/option_select.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'optgroup'}
                                                {include file='tpl_inc/option_optgroup.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'colorpicker'}
                                                {include file='snippets/colorpicker.tpl'
                                                cpID="{$setting->elementID}"
                                                cpName="{$setting->elementID}"
                                                cpValue=$setting->value}
                                            {elseif $setting->cType === 'number'}
                                                {include file='tpl_inc/option_number.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'radio'}
                                                {include file='tpl_inc/option_radio.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'textarea' }
                                                {include file='tpl_inc/option_textarea.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'upload' && isset($setting->rawAttributes.target)}
                                                {include file='tpl_inc/option_upload.tpl' setting=$setting section=$section iteration=$setting@iteration}
                                            {else}
                                                {include file='tpl_inc/option_generic.tpl' setting=$setting section=$section iteration=$setting@iteration}
                                            {/if}
                                        </div>
                                    {else}
                                        <input type="hidden" name="{$setting->elementID}" value="{$setting->value|escape:'html'}" />
                                    {/if}
                                </div>
                            </div>
                        {/foreach}
                    </div>{* /row *}
                </div>
            </div>
        {/foreach}
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <a class="btn btn-outline-primary btn-block" href="shoptemplate.php">
                        {__('cancelWithIcon')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    {if isset($smarty.get.activate)}
                        <input type="hidden" name="activate" value="1" />
                        <input type="hidden" name="action" value="activate" />
                    {else}
                        <input type="hidden" name="action" value="save-config" />
                    {/if}
                    <input type="hidden" name="type" value="settings" />
                    <input type="hidden" name="dir" value="{$template->getDir()}" />
                    <input type="hidden" name="admin" value="0" />
                    <button type="submit" class="btn btn-primary btn-block">
                        {if isset($smarty.get.activate)}<i class="fa fa-share"></i> {__('activateTemplate')}{else}{__('saveWithIcon')}{/if}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
