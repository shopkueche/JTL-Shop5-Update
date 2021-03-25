<div {$instance->getAttributeString()} class="opc-Tabs {$instance->getStyleClasses()}">
    <nav class="tab-navigation">
        {tabs}
        {foreach $instance->getProperty('tabs') as $i => $tabTitle}
            {$tabId = $instance->getUid()|cat:'-'|cat:$i}
            {$areaId = 'tab-'|cat:$i}
            {tab id=$tabId title=$tabTitle|escape:'html' active=$i==0}
                <div data-area-id="{$areaId}" class="opc-area">
                    {if $isPreview}
                        {$instance->getSubareaPreviewHtml($areaId)}
                    {else}
                        {$instance->getSubareaFinalHtml($areaId)}
                    {/if}
                </div>
            {/tab}
        {/foreach}
        {/tabs}
    </nav>
</div>