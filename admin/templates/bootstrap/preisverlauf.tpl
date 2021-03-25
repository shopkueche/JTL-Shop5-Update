{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl'
        cTitel=__('configurePriceFlow')
        cBeschreibung=__('configurePriceFlowDesc')
        cDokuURL=__('configurePriceFlowURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/config_section.tpl'
                    config=$oConfig_arr
                    name='einstellen'
                    a='saveSettings'
                    action='preisverlauf.php'
                    title=__('settings')
                    tab='einstellungen'}
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
