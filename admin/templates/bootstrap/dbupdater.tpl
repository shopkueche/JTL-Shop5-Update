{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('dbupdater') cBeschreibung=__('dbupdaterDesc') cDokuURL=__('dbupdaterURL')}
<div id="content">
    <div class="card">
        <div id="resultLog" class="card-body" {if !$updatesAvailable}style="display: none;"{/if}>
            <h4>{__('eventProtocol')}</h4>
            <pre id="debug">
{__('currentShopVersion')}
    {__('system')}: {$currentFileVersion}
    {__('database')}: {$currentDatabaseVersion}
{if $currentTemplateFileVersion != $currentTemplateDatabaseVersion}
    {__('currentTemplateVersion')}
        {__('system')}: {$currentTemplateFileVersion}
        {__('database')}: {$currentTemplateDatabaseVersion}
{/if}</pre>
            <br /><br />
        </div>
    </div>
    <div>
        {if $hasMinUpdateVersion}
            <div id="update-status">
                {include file='tpl_inc/dbupdater_status.tpl'}
            </div>
        {/if}
    </div>
</div>

{include file='tpl_inc/dbupdater_scripts.tpl'}
{include file='tpl_inc/footer.tpl'}
