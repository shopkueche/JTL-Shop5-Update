{include file='tpl_inc/header.tpl'}
{if $step === 'newvar'}
    {include file='tpl_inc/sprache_newvar.tpl'}
{elseif $step === 'overview'}
    {include file='tpl_inc/sprache_overview.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}
