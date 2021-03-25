{$imgAttribs = $instance->getImageAttributes()}

{if $isPreview && empty($imgAttribs.src)}
    <div class="opc-Image" style="{$instance->getStyleString()}">
        <div>
            <i class="far fa-image"></i>
            <span>{__('Image')}</span>
        </div>
    </div>
{elseif !empty($imgAttribs.src)}
    {$alignCSS = ''}
    {if $instance->getProperty('align') === 'left'}
        {$alignCSS = 'margin-right: auto;'}
    {elseif $instance->getProperty('align') === 'right'}
        {$alignCSS = 'margin-left: auto;'}
    {elseif $instance->getProperty('align') === 'center'}
        {$alignCSS = 'margin-left: auto;margin-right: auto;'}
    {/if}
    {if $isPreview}
        <div class="opc-Image-with-image">
    {/if}
    <div style="max-width: {$imgAttribs.realWidth}px; {$alignCSS}">
        {$isLink = $instance->getProperty('is-link')}
        {$href = $instance->getProperty('url')}

        {if $isLink && !$isPreview && !empty($href)}
            <a href="{$href|escape:'html'}"
                {if !empty($instance->getProperty('link-title'))}
                    title = "{$instance->getProperty('link-title')|escape:'html'}"
                {/if}
                {if $instance->getProperty('new-tab') === true}
                    target = "_blank"
                {/if}>
        {/if}
        {image
            src=$imgAttribs.src
            srcset=$imgAttribs.srcset
            sizes=$imgAttribs.srcsizes
            alt=$imgAttribs.alt|escape:'html'
            title=$imgAttribs.title
            style=$instance->getStyleString()
            rounded=$portlet->getRoundedProp($instance)
            thumbnail=$portlet->getThumbnailProp($instance)
            class=$instance->getStyleClasses()
            fluid-grow=true
        }
        {if $isLink && !$isPreview && !empty($href)}
            </a>
        {/if}
    </div>
    {if $isPreview}
        </div>
    {/if}
{/if}