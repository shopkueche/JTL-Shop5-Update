{include file='tpl_inc/header.tpl'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/einstellungen_uebersicht.tpl'}
{elseif $step === 'einstellungen bearbeiten'}
    {include file='tpl_inc/einstellungen_bearbeiten.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}
