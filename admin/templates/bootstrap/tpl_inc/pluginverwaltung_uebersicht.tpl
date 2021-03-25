<script type="text/javascript">
    function ackCheck(kPlugin, hash)
    {
        var bCheck = confirm('{__('surePluginUpdate')}');
        var href = '';

        if (bCheck) {
            href += 'pluginverwaltung.php?pluginverwaltung_uebersicht=1&updaten=1&token={$smarty.session.jtl_token}&kPlugin=' + kPlugin;
            if (hash && hash.length > 0) {
                href += '#' + hash;
            }
            window.location.href = href;
        }
    }

    {if isset($bReload) && $bReload}
    window.location.href = window.location.href + "?h={$hinweis64}";
    {/if}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('pluginverwaltung') cBeschreibung=__('pluginverwaltungDesc') cDokuURL=__('pluginverwaltungURL')}

<div id="content">
    <div id="settings">
        <div class="tabs">
            <nav class="tabs-nav">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {if $cTab === '' || $cTab === 'aktiviert'} active{/if}" data-toggle="tab" role="tab" href="#aktiviert">
                            {__('activated')}<span class="badge">{$pluginsInstalled->count()}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if $cTab === 'deaktiviert'} active{/if}" data-toggle="tab" role="tab" href="#deaktiviert">
                            {__('deactivated')} <span class="badge">{$pluginsDisabled->count()}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if $cTab === 'probleme'} active{/if}" data-toggle="tab" role="tab" href="#probleme">
                            {__('problems')} <span class="badge">{$pluginsProblematic->count()}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if $cTab === 'verfuegbar'} active{/if}" data-toggle="tab" role="tab" href="#verfuegbar">
                            {__('available')} <span class="badge">{$pluginsAvailable->count()}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if $cTab === 'fehlerhaft'} active{/if}" data-toggle="tab" role="tab" href="#fehlerhaft">
                            {__('faulty')} <span class="badge">{$pluginsErroneous->count()}</span>
                        </a>
                    </li>
                    {if $smarty.const.SAFE_MODE === false}
                    <li class="nav-item">
                        <a class="nav-link {if $cTab === 'upload'} active{/if}" data-toggle="tab" role="tab" href="#upload">{__('upload')}</a>
                    </li>
                    {/if}
                </ul>
            </nav>
            <div class="tab-content">
                {include file='tpl_inc/pluginverwaltung_uebersicht_aktiviert.tpl'}
                {include file='tpl_inc/pluginverwaltung_uebersicht_deaktiviert.tpl'}
                {include file='tpl_inc/pluginverwaltung_uebersicht_probleme.tpl'}
                {include file='tpl_inc/pluginverwaltung_uebersicht_verfuegbar.tpl'}
                {include file='tpl_inc/pluginverwaltung_uebersicht_fehlerhaft.tpl'}
                {if $smarty.const.SAFE_MODE === false}
                {include file='tpl_inc/pluginverwaltung_upload.tpl'}
                {/if}
            </div>
        </div>
    </div>
</div>
