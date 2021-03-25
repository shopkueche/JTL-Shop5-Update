<ul id="{$eContainer}" class="dashboard-col  col-12 col-xl-6 col-xxl-4 sortable">
    {foreach $oActiveWidget_arr as $oWidget}
        {if $oWidget->eContainer === $eContainer}
            <li id="widget-{$oWidget->cClass}" class="widget sortitem card mb-4" ref="{$oWidget->kWidget}">
                <div class="widget-head card-header">
                    <span class="widget-title">{$oWidget->cTitle}</span>
                    <span class="options"></span>
                    <hr class="mb-n3">
                </div>
                <div class="widget-content{if $oWidget->hasBody === true} card-body{/if}{if !$oWidget->bExpanded} widget-hidden{/if}">
                    {$oWidget->cContent}
                </div>
            </li>
        {/if}
    {/foreach}
</ul>
