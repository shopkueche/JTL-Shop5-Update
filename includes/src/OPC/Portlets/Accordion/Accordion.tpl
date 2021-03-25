{$uid = $instance->getUid()}

{accordion id=$uid style=$instance->getStyleString() class=$instance->getStyleClasses()}
    {foreach $instance->getProperty('groups') as $i => $group}
        {$groupId = $uid|cat:'-'|cat:$i}
        {$areaId = 'group-'|cat:$i}

        {if $isPreview}
            <div class="opc-Accordion-group">
                <div id="heading-{$groupId}">
                    <a href="#" data-toggle="collapse" data-target="#{$groupId}" class="opc-Accordion-head collapsed"
                       data-parent="#{$uid}">
                        {$group|escape:'html'} <i class="fas fa-chevron-up"></i>
                    </a>
                </div>
                {collapse
                    id=$groupId
                    visible = $i === 0 && $instance->getProperty('expanded') === true
                    data=['parent' => '#'|cat:$uid]
                    aria=['labelledby' => 'heading-'|cat:$groupId]
                    class='opc-Accordion-collapse'
                }
                    <div class="opc-area" data-area-id="{$areaId}">
                        {$instance->getSubareaPreviewHtml($areaId)}
                    </div>
                {/collapse}
            {*{/card}*}
            </div>
        {else}
            {if $i === 0 && $instance->getProperty('expanded') === true}
                {$ariaExpanded = 'true'}
            {else}
                {$ariaExpanded = 'false'}
            {/if}

            {card no-body=true}
                {cardheader id='heading-'|cat:$groupId}
                    <h2 style="margin-bottom: 0">
                        {button
                            variant='link'
                            data=['toggle' => 'collapse', 'target' => '#'|cat:$groupId, 'parent' => '#'|cat:$uid]
                            aria=['expanded' => $ariaExpanded, 'controls' => $groupId]
                        }
                            {$group|escape:'html'}
                        {/button}
                    </h2>
                {/cardheader}
                {collapse
                    id=$groupId
                    visible = $i === 0 && $instance->getProperty('expanded') === true
                    data=['parent' => '#'|cat:$uid]
                    aria=['labelledby' => 'heading-'|cat:$groupId]
                    class='opc-Accordion-collapse'
                }
                    {cardbody class='opc-area' data=['area-id' => $areaId]}
                        {$instance->getSubareaFinalHtml($areaId)}
                    {/cardbody}
                {/collapse}
            {/card}
        {/if}
    {/foreach}
{/accordion}