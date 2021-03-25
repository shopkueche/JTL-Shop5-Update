{block name='snippets-alert-list'}
    {if count($alertList->getAlertlist()) > 0}
        <div id="alert-list" class="{if $container|default:true}container{/if}">
            {foreach $alertList->getAlertlist() as $alert}
                {if $alert->getShowInAlertListTemplate()}
                    {$alert->display()}
                {/if}
            {/foreach}
        </div>
    {/if}
{/block}
