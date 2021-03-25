{foreach $oAvailableWidget_arr as $oAvailableWidget}
    <a href="#" class="dropdown-item" data-widget-add="1" onclick="addWidget({$oAvailableWidget->kWidget})">
        <div class="row no-gutters">
            <div class="col col-1"><span href="#" class="fal fa-plus text-primary"></span></div>
            <div class="col col-11 font-weight-bold">{$oAvailableWidget->cTitle}</div>
            <div class="col col-1"></div>
            <div class="col col-11">{$oAvailableWidget->cDescription}</div>
        </div>
    </a>
    {if !$oAvailableWidget@last}
        <div class="dropdown-divider"></div>
    {/if}
{/foreach}
{if $oAvailableWidget_arr|@count == 0}
    <span class="ml-3 font-weight-bold">{__('noMoreWidgets')}</span>
{/if}