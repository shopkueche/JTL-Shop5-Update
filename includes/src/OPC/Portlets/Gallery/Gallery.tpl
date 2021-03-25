{$galleryStyle = $instance->getProperty('galleryStyle')}
{$images = $instance->getProperty('images')}

{if $isPreview && empty($images)}
    <div class="opc-Gallery-preview" style="{$instance->getStyleString()}">
        <div>
            {file_get_contents($portlet->getBasePath()|cat:'icon.svg')}
            <span>{__('Gallery')}</span>
        </div>
    </div>
{elseif $galleryStyle === 'columns'}
    <div class="opc-Gallery-columns {$instance->getStyleClasses()}"
         id="{$instance->getUid()}" style="{$instance->getStyleString()}"
         data-colcade="columns: .opc-Gallery-column, items: .opc-Gallery-btn">
        <div class="opc-Gallery-column opc-Gallery-column-1"></div>
        <div class="opc-Gallery-column opc-Gallery-column-2"></div>
        <div class="opc-Gallery-column opc-Gallery-column-3"></div>
        <div class="opc-Gallery-column opc-Gallery-column-4"></div>
        {foreach $images as $key => $image}
            {$imgAttribs = $instance->getImageAttributes($image.url, $image.alt, '')}
            <a {if $isPreview === false}
                    {if $image.action === 'link'}
                        href="{$image.link|escape:'html'}"
                    {elseif $image.action === 'lightbox'}
                        href="{$imgAttribs.src|escape:'html'}"
                    {/if}
               {/if} class="opc-Gallery-btn {if $image.action === 'lightbox'}opc-Gallery-active-btn{/if}"
               data-caption="{$image.desc|escape:'html'}">
                {image class='opc-Gallery-img'
                       srcset=$imgAttribs.srcset
                       sizes=$imgAttribs.srcsizes
                       src=$imgAttribs.src
                       alt=$imgAttribs.alt|escape:'html'
                       title=$imgAttribs.title}
                {if $image.action === 'lightbox'}
                    <i class="opc-Gallery-zoom fa fa-search fa-2x"></i>
                {/if}
            </a>
        {/foreach}
        {if $isPreview}
            {inline_script}<script>
                $('#{$instance->getUid()}').colcade({
                    columns: '.opc-Gallery-column',
                    items: '.opc-Gallery-btn'
                })
            </script>{/inline_script}
        {/if}
    </div>
{else}
    {if $inContainer === false}
        <div class="container-fluid">
    {/if}
    {row
        id=$instance->getUid()
        class='opc-Gallery opc-Gallery-'|cat:$galleryStyle|cat:' '|cat:$instance->getStyleClasses()
        style=$instance->getStyleString()
    }
        {$xsSum = 0}
        {$smSum = 0}
        {$mdSum = 0}
        {$xlSum = 0}
        {foreach $images as $key => $image}
            {if $galleryStyle === 'alternate'}
                {if $image@last}
                    {$image.xs = 12 - $xsSum % 12}
                    {$image.sm = 12 - $smSum % 12}
                    {$image.md = 12 - $mdSum % 12}
                    {$image.xl = 12 - $xlSum % 12}
                {else}
                    {$image.xs = 6}
                    {$image.sm = 5}
                    {$image.md = 3}
                    {$image.xl = 3}
                    {if $key % 3 === 0}
                        {$image.xs = 12}
                    {/if}
                    {if $key % 4 === 0 || $key % 4 === 3}
                        {$image.sm = 7}
                    {/if}
                    {if $key % 6 === 0 || $key % 6 === 5}
                        {$image.md = 5}
                    {elseif $key % 6 === 1 || $key % 6 === 4}
                        {$image.md = 4}
                    {/if}
                    {if $key % 8 === 0 || $key % 8 === 7}
                        {$image.xl = 4}
                    {elseif $key % 8 === 1 || $key % 8 === 5}
                        {$image.xl = 2}
                    {/if}
                {/if}
                {$xsSum = $xsSum + $image.xs}
                {$smSum = $smSum + $image.sm}
                {$mdSum = $mdSum + $image.md}
                {$xlSum = $xlSum + $image.xl}
            {elseif $galleryStyle === 'grid'}
                {$image.xs = 6}
                {$image.sm = 4}
                {$image.md = 3}
                {$image.xl = 2}
            {/if}

            {$image.lg = $image.md}

            {$imgAttribs = $instance->getImageAttributes($image.url, $image.alt, '',['xs'=>$image.xs,'sm'=>$image.sm,'md'=>$image.md,'lg'=>$image.lg,'xl'=>$image.xl])}
            {col cols=$image.xs sm=$image.sm md=$image.md lg=$image.lg xl=$image.xl class="opc-Gallery-item"}
                <a {if $isPreview === false}
                        {if $image.action === 'link'}
                            href="{$image.link|escape:'html'}"
                        {elseif $image.action === 'lightbox'}
                            href="{$imgAttribs.src|escape:'html'}"
                        {/if}
                    {/if}
                   class="opc-Gallery-btn {if $image.action === 'lightbox'}opc-Gallery-active-btn{/if}"
                   data-caption="{$image.desc|escape:'html'}"
                   aria-label="{$image.alt}"
                >
                    {image class='opc-Gallery-img'
                           srcset=$imgAttribs.srcset
                           sizes=$imgAttribs.srcsizes
                           src=$imgAttribs.src
                           alt=$imgAttribs.alt|escape:'html'
                           title=$imgAttribs.title}
                    {if $image.action === 'lightbox'}
                        <i class="opc-Gallery-zoom fa fa-search fa-2x"></i>
                    {/if}
                </a>
            {/col}
        {/foreach}
    {/row}
    {if $inContainer === false}
        </div>
    {/if}
{/if}

{if $isPreview === false}
    {inline_script}<script>
        var initGallery = function() {
            $('#{$instance->getUid()}').slickLightbox({
                itemSelector: '.opc-Gallery-active-btn',
                caption: 'caption',
                lazy: true,
            });
        };

        $(initGallery);
    </script>{/inline_script}
{/if}