{if $isPreview}
    <div {$instance->getAttributeString()} class="opc-Video" style="position: relative">
        {if !empty($instance->getProperty('video-responsive'))}
            {$style = 'width:100%;'}
        {else}
            {$style = 'width:'}
            {$style = $style|cat:$instance->getProperty('video-width')}
            {$style = $style|cat:'px;height:'}
            {$style = $style|cat:$instance->getProperty('video-height')}
            {$style = $style|cat:'px'}
        {/if}

        {$src = $portlet->getPreviewImageUrl($instance)}

        {if $src !== null && $instance->getProperty('video-vendor') === 'youtube'}
            {image src=$src alt='YouTube Video' fluid=true style=$style}
            <div class="give-consent-preview" style="background-image: url({$portlet->getPreviewOverlayUrl()})"></div>
        {elseif $src !== null && $instance->getProperty('video-vendor') === 'vimeo'}
            {image src=$src alt='Vimeo Video' fluid=true style=$style}
            <div class="give-consent-preview" style="background-image: url({$portlet->getPreviewOverlayUrl()})"></div>
        {else}
            <div>
                <i class="fas fa-film"></i>
                <span>{__('Video')}</span>
            </div>
        {/if}
    </div>
{else}
    {$previewImageUrl = $portlet->getPreviewImageUrl($instance)}

    <div id="{$instance->getUid()}" {$instance->getAttributeString()} class="opc-Video {$instance->getStyleClasses()}">
        {if !empty($instance->getProperty('video-title'))}
            <label>{$instance->getProperty('video-title')|escape:'html'}</label>
        {/if}
        {if $instance->getProperty('video-vendor') === 'youtube'}
            <div{if $instance->getProperty('video-responsive')}
                    class="embed-responsive embed-responsive-16by9"{/if}>
                <iframe {strip}
                    data-src="https://www.youtube-nocookie.com/embed/{$instance->getProperty('video-yt-id')|escape:'html'}
                    ?controls={$instance->getProperty('video-yt-controls')}
                    &loop={$instance->getProperty('video-yt-loop')}
                    &rel={$instance->getProperty('video-yt-rel')}
                    &showinfo=0&color={$instance->getProperty('video-yt-color')}
                    &iv_load_policy=3
                    &playlist={$instance->getProperty('video-yt-playlist')|escape:'html'}
                    {if !empty($instance->getProperty('video-yt-start'))}&start={$instance->getProperty('video-yt-start')}{/if}
                    {if !empty($instance->getProperty('video-yt-end'))}&end={$instance->getProperty('video-yt-end')}{/if}"
                    {/strip}
                        class="needs-consent youtube
                            {if $instance->getProperty('video-responsive')}embed-responsive-item{/if}"
                        {if !empty($instance->getProperty('video-title'))}
                            title="{$instance->getProperty('video-title')|escape:'html'}"
                        {/if}
                        {if !$instance->getProperty('video-responsive')}
                            width="{$instance->getProperty('video-width')}"
                            height="{$instance->getProperty('video-height')}"
                        {/if}
                        allowfullscreen></iframe>
                <a href="#" class="trigger give-consent give-consent-preview"
                   data-consent="youtube"
                   style="background-image:
                           url({$portlet->getPreviewOverlayUrl()})
                           {if $previewImageUrl !== null},url({$previewImageUrl});{/if}">
                    {lang key='allowConsentYouTube'}
                </a>
            </div>
        {elseif $instance->getProperty('video-vendor') === 'vimeo'}
            <div{if $instance->getProperty('video-responsive')}
                    class="embed-responsive embed-responsive-16by9"{/if}>
                <iframe {strip}
                    data-src="https://player.vimeo.com/video/{$instance->getProperty('video-vim-id')|escape:'html'}
                    ?color={$instance->getProperty('video-vim-color')|replace:'#':''}
                    &portrait={$instance->getProperty('video-vim-img')}
                    &title={$instance->getProperty('video-vim-title')|escape:'html'}
                    &byline={$instance->getProperty('video-vim-byline')}
                    &loop={$instance->getProperty('video-vim-loop')}"
                    {/strip}
                        class="needs-consent vimeo
                            {if $instance->getProperty('video-responsive')}embed-responsive-item{/if}"
                        allowfullscreen
                        {if !empty($instance->getProperty('video-title'))}
                            title="{$instance->getProperty('video-title')}"
                        {/if}
                        {if !$instance->getProperty('video-responsive')}
                            width="{$instance->getProperty('video-width')}"
                            height="{$instance->getProperty('video-height')}"
                        {/if}></iframe>
                <a href="#" class="trigger give-consent give-consent-preview"
                   data-consent="vimeo"
                   style="background-image:
                           url({$portlet->getPreviewOverlayUrl()})
                           {if $previewImageUrl !== null},url({$previewImageUrl});{/if}">
                    {lang key='allowConsentVimeo'}
                </a>
            </div>
        {else}
            <div{if $instance->getProperty('video-responsive')} class="embed-responsive embed-responsive-16by9"{/if}>
                <video {if $instance->getProperty('video-width')}width="{$instance->getProperty('video-width')}"{/if}
                       {if $instance->getProperty('video-height')}height="{$instance->getProperty('video-height')}"{/if}
                       {if $instance->getProperty('video-local-autoplay')} autoplay{/if}
                       {if $instance->getProperty('video-local-mute')} muted{/if}
                       {if $instance->getProperty('video-local-loop')} loop{/if}
                       {if $instance->getProperty('video-local-controls')} controls{/if} style="">
                    <source src="{$instance->getProperty('video-local-url')|escape:'html'}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        {/if}
    </div>
{/if}