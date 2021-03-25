{include file='tpl_inc/header.tpl'}
{if $pluginNotFound === true}
<div class="alert alert-danger">{__('pluginNotFound')}</div>
{else}
    {if $step === 'pluginverwaltung_uebersicht'}
        {include file='tpl_inc/pluginverwaltung_uebersicht.tpl'}
    {elseif $step === 'pluginverwaltung_sprachvariablen'}
        {include file='tpl_inc/pluginverwaltung_sprachvariablen.tpl'}
    {elseif $step === 'pluginverwaltung_lizenzkey'}
        {include file='tpl_inc/pluginverwaltung_lizenzkey.tpl'}
    {/if}
{/if}
{include file='tpl_inc/footer.tpl'}
