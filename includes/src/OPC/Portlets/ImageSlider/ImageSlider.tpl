{if $isPreview}
    {$slides = $instance->getProperty('slides')}
    {if $slides|count > 0}
        {$imgAttribs = $instance->getImageAttributes($slides[0].url, '', '')}
    {/if}
    <div class="text-center opc-ImageSlider {if $slides|count > 0}opc-ImageSlider-with-image{/if}"
         style="{if $slides|count > 0}background-image: url('{$imgAttribs.src}');{/if} {$instance->getStyleString()}">
        <div>
            {file_get_contents($portlet->getBasePath()|cat:'icon.svg')}
            <span>{__('Bilder-Slider')}</span>
        </div>
    </div>
{else}
    {$uid = $instance->getUid()}

    <div style="{$instance->getStyleString()}" class="{$instance->getStyleClasses()}">
        {if $instance->getProperty('slides')|count > 0}
            <div class="theme-{$instance->getProperty('slider-theme')}">
                <div id="{$uid}" class="nivoSlider">
                    {foreach $instance->getProperty('slides') as $i => $slide}
                        {if !empty($slide.title) || !empty($slide.desc)}
                            {$slideTitle = '#'|cat:$uid|cat:'_slide_caption_'|cat:$i}
                        {else}
                            {$slideTitle = ''}
                        {/if}

                        {if !empty($slide.url)}
                            {$imgAttribs = $instance->getImageAttributes($slide.url, '', '')}

                            {if !empty($slide.link)}
                                <a href="{$slide.link}"
                                   {if !empty($slide.title)}title="{$slide.title|escape:'html'}"{/if}
                                   class="slide">
                            {else}
                                <div class="slide">
                            {/if}
                            {image
                                srcset=$imgAttribs.srcset
                                sizes=$imgAttribs.srcsizes
                                src=$imgAttribs.src
                                alt=$imgAttribs.alt|escape:'html'
                                title=$slideTitle|escape:'html'
                                data=['desc' => $slide.desc|escape:'html']}
                            {if empty($slide.link)}
                                </div>
                            {else}
                                </a>
                            {/if}
                        {/if}
                    {/foreach}
                </div>
                {foreach $instance->getProperty('slides') as $i => $slide}
                    {if !empty($slide.title) || !empty($slide.desc)}
                        <div id="{$uid}_slide_caption_{$i}" class="htmlcaption" style="display: none">
                            {if !empty($slide.title)}
                                <strong class="title">{$slide.title|escape:'html'}</strong>
                            {/if}
                            <p class="desc">{$slide.desc|escape:'html'}</p>
                        </div>
                    {/if}
                {/foreach}
            </div>
            {inline_script}<script>
                {if !empty($instance->getProperty('slider-kenburns'))}
                    // pauseTime must be set here
                    var pauseTime      = {$instance->getProperty('slider-animation-pause')};
                    // animSpeed must be set here
                    var animSpeed      = {$instance->getProperty('slider-animation-speed')};
                    // 30% zoom as default
                    var zoomFactor     = 30;
                    // firstslide pausetime adjustment factor
                    var durationFactor = 1.25;

                    function KBInit()
                    {
                        $('#{$uid} img').css('visibility', 'hidden');
                        $('#{$uid} .nivo-nextNav').trigger('click');
                        $('#{$uid} , .nivo-control').css('opacity', 1);

                        setTimeout(function () {
                            $('#{$uid} , .nivo-control')
                                .animate({ opacity: 1 }, animSpeed);
                        }, 0);

                        $('#{$uid} .nivo-control').on('click', function () {
                            setTimeout(function () {
                                $('#{$uid} .nivo-main-image').css('opacity', 0);
                            }, 0);

                            durationFactor = 1.25;
                        });

                        $('#{$uid} .nivo-prevNav, #{$uid} .nivo-nextNav').on('click', function () {
                            setTimeout(function () {
                                $('#{$uid} .nivo-main-image').css('opacity', 0);
                            }, 20);

                            durationFactor = 1.25;
                        });
                    }

                    function NivoKenBurns()
                    {
                        $('#{$uid} .nivo-main-image').css('opacity', 1);

                        setTimeout(function () {
                            $('#{$uid}  .nivo-slice img').css('width', 100 + zoomFactor + '%');
                        }, 10);

                        setTimeout(function () {
                            var nivoWidth  = $('#{$uid} ').width();
                            var nivoHeight = $('#{$uid} ').height();
                            var xScope = nivoWidth * zoomFactor / 100;
                            var yScope = nivoHeight * zoomFactor / 105;
                            var xStart = -xScope * Math.floor(Math.random() * 2);
                            var yStart = -yScope * Math.floor(Math.random() * 2);

                            $('#{$uid} .nivo-slice img')
                                .css('left', xStart)
                                .css('top', yStart)
                                .animate(
                                    {
                                        width: '100%',
                                        left:  0,
                                        top:   0
                                    },
                                    pauseTime * durationFactor
                                );

                            durationFactor = 1.02;

                            $('#{$uid} .nivo-main-image').css('cssText', 'left:0 !important;top:0 !important;');
                        }, 10);
                    }

                    var initImageSlider = function () {
                        var slider   = $('#{$uid}');
                        var endSlide = $('#{$uid}  img').length - 1;

                        $('a.slide').click(function () {
                            if (!this.href.match(new RegExp('^' + location.protocol + '\\/\\/' + location.host))) {
                                this.target = '_blank';
                            }
                        });

                        slider.nivoSlider({
                            effect:           'fade',
                            animSpeed:        animSpeed,
                            pauseTime:        pauseTime,
                            directionNav:     true,
                            controlNav:       false,
                            controlNavThumbs: false,
                            pauseOnHover:     false,
                            prevText:         'prev',
                            nextText:         'next',
                            manualAdvance:    false,
                            randomStart:      false,
                            startSlide:       endSlide,
                            slices:           1,
                            beforeChange:     function () {
                                NivoKenBurns();
                            },
                            afterLoad:        function () {
                                KBInit();
                                slider.addClass('loaded');
                            }
                        });
                    };
                {else}
                    var initImageSlider = function () {
                        var slider = $('#{$uid}');

                        $('a.slide').click(function () {
                            if (!this.href.match(new RegExp('^' + location.protocol + '\\/\\/' + location.host))) {
                                this.target = '_blank';
                            }
                        });

                        slider.nivoSlider({
                            effect:
                                {if !empty($instance->getProperty('slider-effects-random'))
                                        && $instance->getProperty('slider-effects-random') === 'true'}
                                    'random',
                                {else}
                                    '{$portlet->getEnabledEffectList($instance)}',
                                {/if}
                            animSpeed:
                                {if !empty($instance->getProperty('slider-animation-speed'))}
                                    {$instance->getProperty('slider-animation-speed')},
                                {else}
                                    500,
                                {/if}
                            pauseTime:
                                {if !empty($instance->getProperty('slider-animation-pause'))}
                                    {$instance->getProperty('slider-animation-pause')},
                                {else}
                                    3000,
                                {/if}
                            directionNav:     {$instance->getProperty('slider-direction-navigation')},
                            controlNav:       {$instance->getProperty('slider-navigation')},
                            controlNavThumbs: false,
                            pauseOnHover:     {$instance->getProperty('slider-pause')},
                            prevText:         'prev',
                            nextText:         'next',
                            randomStart:      true,
                            afterLoad:        function () {
                                slider.addClass('loaded');
                            }
                        });
                    };
                {/if}

                $(initImageSlider);
            </script>{/inline_script}
        {/if}
    </div>
{/if}