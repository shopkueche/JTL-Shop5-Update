{include file='tpl_inc/header.tpl'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/exportformat_queue_uebersicht.tpl'}
{elseif $step === 'erstellen'}
    {include file='tpl_inc/exportformat_queue_erstellen.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}
