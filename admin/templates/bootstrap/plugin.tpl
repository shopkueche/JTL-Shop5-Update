{include file='tpl_inc/header.tpl'}
{if $pluginNotFound === true}
    <div class="alert alert-danger">{__('pluginNotFound')}</div>
{elseif $step === 'plugin_uebersicht'}
    {include file='tpl_inc/plugin_uebersicht.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}
