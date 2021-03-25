{function name=slide level=0}
    <tr class="text-vcenter" id="slide{$kSlide}">
        <td class="text-center">
            <input type="hidden" id="aSlide{$kSlide}delete" name="aSlide[{$kSlide}][delete]" value="0" />
            <input type="hidden" name="aSlide[{$kSlide}][cBild]" value="{if isset($oSlide)}{$oSlide->getImage()}{/if}" />
            <input type="hidden" name="aSlide[{$kSlide}][cThumbnail]" value="{if isset($oSlide)}{$oSlide->getThumbnail()}{/if}" />
            <input type="hidden" class="form-control" id="aSlide[{$kSlide}][nSort]" name="aSlide[{$kSlide}][nSort]" value="{if $kSlide}{$smarty.foreach.slide.iteration}{/if}" autocomplete="off" />
            <i class="btn btn-primary fa fa-bars"></i>
        </td>
        <td class="text-center">
            <img src="{if isset($oSlide)}{$oSlide->getAbsoluteImage()}{else}templates/bootstrap/gfx/layout/upload.png{/if}"
                 id="img{$kSlide}" onclick="select_image('{$kSlide}');"
                 alt="Slidergrafik" class="slide-image" role="button">
        </td>
        <td class="text-center min-w-sm">
            <input class="form-control margin2" id="cTitel{$kSlide}" type="text" name="aSlide[{$kSlide}][cTitel]"
                   value="{if isset($oSlide)}{$oSlide->getTitle()}{/if}" placeholder="{__('title')}">
            <input class="form-control margin2" id="cLink{$kSlide}" type="text" name="aSlide[{$kSlide}][cLink]"
                   value="{if isset($oSlide)}{$oSlide->getLink()}{/if}" placeholder="{__('link')}">
        </td>
        <td class="min-w-sm">
            <textarea class="form-control vheight" id="cText{$kSlide}" name="aSlide[{$kSlide}][cText]"
                      maxlength="255" placeholder="{__('text')}">{if isset($oSlide)}{$oSlide->getText()}{/if}</textarea>
        </td>
        <td class="vcenter">
            <button type="button"
                    onclick="$(this).parent().parent().find('input[name*=\'delete\']').val('1'); $(this).parent().parent().css({ 'display':'none'});sortSlide();"
                    class="slide_delete btn"
                    title="{__('delete')}"
                    data-toggle="tooltip">
                <span class="icon-hover">
                    <span class="fal fa-trash-alt"></span>
                    <span class="fas fa-trash-alt"></span>
                </span>
            </button>
        </td>
    </tr>
{/function}

<form id="slide{$kSlider}" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="slide_set" />
    {$jtl_token}
    <div id="settings">
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('slider')}</div>
                <hr class="mb-n3">
            </div>
            <div class="table-responsive card-body">
                <table id="tableSlide" class="table">
                    <thead>
                    <tr>
                        <th class="text-left"></th>
                        <th width="10%">{__('Image')}</th>
                        <th width="40%">{__('title')} / {__('link')}</th>
                        <th width="40%">{__('text')}</th>
                        <th width="5%"></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $oSlider->getSlides() as $oSlide}
                        {slide kSlide=$oSlide->getID() oSlide=$oSlide}
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <table class="hidden"><tbody id="newSlide">{slide oSlide=null kSlide='NEU'}</tbody></table>
            <div class="card-footer">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-outline-primary btn-block mb-2"
                                onclick="window.location.href = 'slider.php';">
                            <i class="fa fa-angle-double-left"></i> {__('goBack')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-outline-primary btn-block mb-2"
                                onclick="location.reload();">
                            {__('cancelWithIcon')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-outline-primary btn-block mb-2"
                                onclick="addSlide();">
                            <i class="glyphicon glyphicon-plus"></i> {__('add')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" class="btn btn-primary btn-block" id="saveButton">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
