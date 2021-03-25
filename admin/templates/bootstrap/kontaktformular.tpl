{include file='tpl_inc/header.tpl'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/kontaktformular_uebersicht.tpl'}
{elseif $step === 'betreff'}
    {include file='tpl_inc/kontaktformular_betreff.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}
