{assign var=cPlugin value=__('plugin')}
{if $oPlugin !== null}
    {include file='tpl_inc/seite_header.tpl' cTitel=$cPlugin|cat:': '|cat:$oPlugin->getMeta()->getName()
        pluginMeta=$oPlugin->getMeta()}
{/if}
<div id="content">
    <div class="container2">
        <div id="update-status">
            {include file='tpl_inc/dbupdater_status.tpl' migrationURL='plugin.php' pluginID=$oPlugin->getID()}
            {include file='tpl_inc/dbupdater_scripts.tpl'}
        </div>
        {assign var=hasActiveMenuTab value=false}
        {assign var=hasActiveMenuItem value=false}
        <div class="tabs" id="tabs-{$oPlugin->getPluginID()}">
            {if $oPlugin !== null && $oPlugin->getAdminMenu()->getItems()->count() > 0}
                <nav class="tabs-nav">
                    <ul class="nav nav-tabs" role="tablist">
                        {foreach $oPlugin->getAdminMenu()->getItems()->toArray() as $menuItem}
                            <li class="tab-{$menuItem->id} nav-item">
                                <a class="tab-link-{$menuItem->id} nav-link {if ($defaultTabbertab === -1 && $menuItem@index === 0) || ($defaultTabbertab > -1 && ($defaultTabbertab === $menuItem->id || $defaultTabbertab === $menuItem->name))} {assign var=hasActiveMenuTab value=true}active{/if}" data-toggle="tab" role="tab" href="#plugin-tab-{$menuItem->id}">
                                    {__($menuItem->displayName)}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </nav>
                <div class="tab-content">
                    {foreach $oPlugin->getAdminMenu()->getItems()->toArray() as $menuItem}
                        <div id="plugin-tab-{$menuItem->id}" class="settings tab-pane fade {if ($defaultTabbertab === -1 && $menuItem@index === 0) || $defaultTabbertab > -1 && ($defaultTabbertab === $menuItem->id || $defaultTabbertab === $menuItem->name)} {assign var=hasActiveMenuItem value=true}active show{/if}">
                            {$menuItem->html}
                        </div>
                    {/foreach}
                </div>
            {else}
                <div class="alert alert-info" role="alert">
                    <i class="fal fa-info-circle"></i> {__('noPluginDataAvailable')}
                </div>
            {/if}
        </div>
    </div>
</div>
