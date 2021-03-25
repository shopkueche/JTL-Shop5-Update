{$uid = $instance->getUid()}
{$trigger = $instance->getProperty('flip-trigger')}

<div id="{$uid}" {$instance->getAnimationDataAttributeString()}
     class="opc-Flipcard opc-Flipcard-{$instance->getProperty('flip-dir')}
            {$instance->getAnimationClass()} {$instance->getStyleClasses()}"
     style="{$instance->getStyleString()}">
    {if $isPreview}
        <a href="#" class="opc-Flipcard-flip-btn opc-no-disable">
            <span class="opc-Flipcard-label opc-Flipcard-label-front active">{__('flipcardFront')}</span>
            <i class="fas fa-exchange-alt"></i>
            <span class="opc-Flipcard-label opc-Flipcard-label-back">{__('flipcardBack')}</span>
        </a>
    {/if}
    <div class="opc-Flipcard-inner">
        <div class="opc-Flipcard-face opc-Flipcard-front {if $isPreview}opc-area{/if}"
             {if $isPreview}data-area-id="front"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("front")}
            {else}
                {$instance->getSubareaFinalHtml("front")}
            {/if}
        </div>
        <div class="opc-Flipcard-face opc-Flipcard-back {if $isPreview}opc-area{/if}"
             {if $isPreview}data-area-id="back"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("back")}
            {else}
                {$instance->getSubareaFinalHtml("back")}
            {/if}
        </div>
    </div>
    <script>
        document.getElementById('{$uid}').updateFlipcardHeight = updateHeight_{$uid};

        function initFlipcard_{$uid}()
        {
            var flipcard      = $('#{$uid}');
            var flipcardInner = flipcard.find('.opc-Flipcard-inner');

            {if $isPreview}
                flipcard.find('.opc-Flipcard-flip-btn').on('click', flipCard);
            {else}
                {if $trigger === 'click'}
                    flipcard.on('click', flipCard);
                {else}
                    flipcard.on('mouseenter mouseleave', flipCard);
                {/if}
            {/if}

            setTimeout(() => updateHeight_{$uid}());

            function flipCard(e)
            {
                {if $trigger === 'click'}
                    let isLink = e.target.tagName === 'A' && typeof e.target.href === 'string'
                        || e.target.tagName === 'BUTTON';

                    if(!isLink) {
                {/if}
                        flipcardInner.toggleClass('opc-Flipcard-flipped');
                        flipcard.find('.opc-Flipcard-label-front').toggleClass('active');
                        flipcard.find('.opc-Flipcard-label-back').toggleClass('active');
                        updateHeight_{$uid}();
                        e.preventDefault();
                {if $trigger === 'click'}
                    }
                {/if}
            }
        }

        function updateHeight_{$uid}()
        {
            let flipcard      = $('#{$uid}');
            let flipcardInner = flipcard.find('.opc-Flipcard-inner');
            let flipcardFaces = flipcardInner.find('.opc-Flipcard-face');
            let height        = 0;

            flipcardInner.css('height', 'auto');
            flipcardFaces.css('height', 'auto');

            flipcardInner.find('.opc-Flipcard-face').each((i, elm) => {
                height = Math.max(height, $(elm).height());
            });

            flipcardInner.height(height);
            flipcardFaces.height(height);
        }
    </script>

    {inline_script}<script>
        $(initFlipcard_{$uid});
    </script>{/inline_script}
</div>