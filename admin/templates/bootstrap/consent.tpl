{include file='tpl_inc/header.tpl'}

{if $step === 'overview'}
    {include file='tpl_inc/model_list.tpl' items=$models select=true edit=true search=true delete=true}
{elseif $step === 'detail'}
    {include file='tpl_inc/model_detail.tpl' item=$item select=true edit=true search=true delete=true save=true enable=true disable=true}
{/if}

{include file='tpl_inc/footer.tpl'}
