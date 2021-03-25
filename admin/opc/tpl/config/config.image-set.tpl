{$useLinks    = $propdesc.useLinks|default:false}
{$useLightbox = $propdesc.useLightbox|default:false}
{$useTitles   = $propdesc.useTitles|default:false}

{function slideEntry
    slideData=['desc' => '', 'url' => '', 'link' => '', 'title' => '', 'action' => 'lightbox']
}
    <div class="slide-entry">
        <div class="slide-btns">
            <span class="btn-slide-mover"
                 title="{__('entryMove')}" style="cursor: move">
                <i class="fas fa-arrows-alt fa-fw"></i>
            </span>
            <button type="button" onclick="cloneSlide_{$propname}()" title="Copy">
                <i class="far fa-clone fa-fw"></i>
            </button>
            <hr>
            <button type="button" onclick="removeSlide_{$propname}()"
                    title="{__('entryDelete')}">
                <i class="far fa-trash-alt fa-fw"></i>
            </button>
        </div>
        <div class="slide-image-col">
            {if empty($slideData.url)}
                {$imgUrl = 'opc/gfx/upload-stub.png'}
            {else}
                {$imgUrl = \JTL\Shop::getURL()|cat:'/'|cat:$smarty.const.STORAGE_OPC|cat:$slideData.url}
            {/if}
            <div style="background-image: url('{$imgUrl}')" class="slide-image-btn"
                 onclick="opc.gui.openElFinder(elfinderCallback_{$propname}.bind(this), 'Bilder')">

            </div>
            <input type="hidden" name="{$propname}[#SORT#][url]" value="{$slideData.url|default:''}">
        </div>
        <div class="slide-props">
            {if $useTitles}
                <input type="text" class="form-control" placeholder="{__('title')}"
                       name="{$propname}[#SORT#][title]" value="{$slideData.title|default:''|escape:'html'}">
            {/if}
            <input type="text" class="form-control" placeholder="{__('description')}"
                   name="{$propname}[#SORT#][desc]" value="{$slideData.desc|default:''|escape:'html'}"
                   maxlength="256">
            <input type="text" class="form-control" placeholder="{__('alternativeText')}"
                   name="{$propname}[#SORT#][alt]" value="{$slideData.alt|default:''|escape:'html'}">
            {if $useLinks}
                <div class="row">
                    <div class="col-4">
                        <label class="select-wrapper">
                            <input type="hidden" name="{$propname}[#SORT#][action]" value="">
                            <select class="form-control" onchange="onActionChange_{$propname}(this)">
                                <option value="none" {if $slideData.action === 'none'}selected{/if}>
                                    {__('noAction')}
                                </option>
                                {if $useLightbox}
                                    <option value="lightbox" {if $slideData.action === 'lightbox'}selected{/if}>
                                        {__('lightbox')}
                                    </option>
                                {/if}
                                <option value="link" {if $slideData.action === 'link'}selected{/if}>
                                    {__('linked')}
                                </option>
                            </select>
                        </label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" placeholder="{__('link')}"
                               name="{$propname}[#SORT#][link]" value="{$slideData.link|default:''|escape:'html'}"
                               {if $slideData.action !== 'link'}disabled{/if}>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/function}

<label>{$propdesc.label}</label>

<div class="slides-container" id="{$propname}-slides-container">
    <div id="{$propname}-slides">
        {foreach $propval as $slideData}
            {if empty($slideData.action)}
                {$slideData.action = 'lightbox'}
            {/if}
            {slideEntry slideData=$slideData}
        {/foreach}
        {if $propval|count === 0}
            {slideEntry}
        {/if}
    </div>
    <div style="display: none" id="{$propname}-slide-blueprint">
        {slideEntry}
    </div>
</div>

<button type="button" class="opc-btn-primary opc-small-btn add-slide-btn" onclick="addSlide_{$propname}()"
        title="{__('imageAdd')}">
    <i class="fas fa-plus fa-fw"></i>
</button>

<script>
    opc.once('save-config', saveImageSet_{$propname});

    $(function () {
        $('#{$propname}-slides').sortable({
            handle: '.btn-slide-mover'
        });
    });

    function elfinderCallback_{$propname}(file)
    {
        let url = file.url.slice(file.baseUrl.length);
        let image = $(this);
        image.css('background-image', 'url("' + file.url + '")');
        image.siblings('input').val(url);
    }

    function addSlide_{$propname}()
    {
        $('#{$propname}-slides').append(
            $('#{$propname}-slide-blueprint').children().clone()
        );
        let slideContainer = $('#{$propname}-slides-container');
        slideContainer[0].scrollTo(0, slideContainer[0].scrollHeight);
    }

    function removeSlide_{$propname}()
    {
        $(event.target).closest('.slide-entry').remove();
    }

    function cloneSlide_{$propname}()
    {
        let slideEntry = $(event.target).closest('.slide-entry');
        let copy = slideEntry.clone();

        copy.insertAfter(slideEntry);
    }

    function saveImageSet_{$propname}()
    {
        $('#{$propname}-slides').children().each((i, slide) => {
            slide = $(slide);

            slide.find('select').each((j, select) => {
                $(select).siblings('input').attr('value',
                    select.options[select.selectedIndex].value
                );
            });

            slide.find('input').each((j, input) => {
                input = $(input);
                var name = input.attr('name');
                if (name === '{$propname}[#SORT#][url]') {
                    var val = input.val();
                    if (val === '') {
                        slide.remove();
                        return;
                    }
                }
                name = name.replace(/#SORT#/, i);
                input.attr('name', name);
            });
        });
        $('#{$propname}-slide-blueprint').remove();
    }

    function onActionChange_{$propname}(elm)
    {
        elm = $(elm);

        if(elm.val() === 'link') {
            elm.closest('.row').find('input[type=text]').prop('disabled', false);
        } else {
            elm.closest('.row').find('input[type=text]').prop('disabled', true);
        }
    }
</script>
