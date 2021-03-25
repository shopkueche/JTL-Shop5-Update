{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('slider') cBeschreibung=__('sliderDesc') cDokuURL=__('sliderURL')}

<script src="{$templateBaseURL}js/slider.js" type="text/javascript"></script>
<div id="content">
    {if $action === 'new' || $action === 'edit' }
        {include file='tpl_inc/slider_form.tpl'}
    {elseif $action === 'slides'}
        {include file='tpl_inc/slider_slide_form.tpl'}
    {else}
        <div id="settings">
            <div class="card">
                {if $oSlider_arr|@count == 0}
                    <div class="card-body">
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    </div>
                {else}
                    <div class="card-header">
                        <div class="subheading1">{__('slider')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        {include file='tpl_inc/pagination.tpl' pagination=$pagination}
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th class="text-left" width="50%">{__('name')}</th>
                                    <th class="text-center" width="20%">{__('active')}</th>
                                    <th width="30%" class="text-center">{__('options')}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $oSlider_arr as $oSlider}
                                    <tr>
                                        <td class="text-left">{$oSlider->cName}</td>
                                        <td class="text-center">
                                            {if $oSlider->bAktiv == 1}
                                                <i class="fal fa-check text-success"></i>
                                            {else}
                                                <i class="fal fa-times text-danger"></i>
                                            {/if}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a class="btn btn-link px-2"
                                                   href="slider.php?action=delete&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}"
                                                   title="{__('delete')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-trash-alt"></span>
                                                        <span class="fas fa-trash-alt"></span>
                                                    </span>
                                                </a>
                                                <a class="btn btn-link px-2 add"
                                                   href="slider.php?action=slides&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}"
                                                   title="{__('slides')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-images"></span>
                                                        <span class="fas fa-images"></span>
                                                    </span>
                                                </a>
                                                <a class="btn btn-link px-2"
                                                   href="slider.php?action=edit&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}"
                                                   title="{__('modify')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-edit"></span>
                                                        <span class="fas fa-edit"></span>
                                                    </span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                {/if}
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a class="btn btn-primary btn-block" href="slider.php?action=new&token={$smarty.session.jtl_token}">
                                <i class="fa fa-share"></i> {__('sliderCreate')}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
