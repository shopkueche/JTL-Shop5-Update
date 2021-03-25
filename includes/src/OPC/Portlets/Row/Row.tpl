{$data = $instance->getAnimationData()}

{if $isPreview}
    {$areaClass = 'opc-area opc-col'}
{/if}

{if $inContainer === false}
    <div class="container-fluid">
{/if}

{row data=$data|default:[]
     class=$instance->getAnimationClass()|cat:' '|cat:$instance->getStyleClasses()
     style=$instance->getStyleString()|default:null}
    {foreach $portlet->getLayouts($instance) as $i => $colLayout}
        {assign var=areaId value="col-$i"}
        {col class=$areaClass|default:null
                 cols=$colLayout.xs|default:false
                 md=$colLayout.sm|default:false
                 lg=$colLayout.md|default:false
                 xl=$colLayout.lg|default:false
                 data=['area-id' => $areaId]}
            {if $isPreview}
                {$instance->getSubareaPreviewHtml($areaId)}
            {else}
                {$instance->getSubareaFinalHtml($areaId)}
            {/if}
        {/col}
        {foreach $colLayout.divider as $size => $value}
            {if !empty($value)}
                {clearfix visible-size=$size}
            {/if}
        {/foreach}
    {/foreach}
{/row}

{if $inContainer === false}
    </div>
{/if}