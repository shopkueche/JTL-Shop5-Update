{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('rssSettings') cBeschreibung=__('rssDescription') cDokuURL=__('rssURL')}
<div id="content">
    {if !$alertError}
        <div class="card">
            <div class="card-body">
                <a href="rss.php?f=1&token={$smarty.session.jtl_token}"><span class="btn btn-primary" style="margin-bottom: 15px;">{__('xmlCreate')}</span></a>
            </div>
        </div>
    {/if}
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='rss.php' buttonCaption=__('saveWithIcon') title=__('settings') tab='einstellungen'}
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
