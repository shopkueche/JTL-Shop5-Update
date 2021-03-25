{includeMailTemplate template=header type=plain}

Shop: {$Einstellungen.global.global_shopname}

Item: {$Artikel->cName}

Email address of customer: {$Nachricht->cMail}

Question: {$Nachricht->cNachricht}

{if !empty($Nachricht->cVorname) || !empty($Nachricht->cNachname) || !empty($Nachricht->cFirma)}
    Customer data:
    {if !empty($Nachricht->cVorname)}{$Nachricht->cVorname} {/if}
    {if !empty($Nachricht->cNachname)}{$Nachricht->cNachname}{/if}
    {if !empty($Nachricht->cFirma)}{$Nachricht->cFirma}{/if}
{/if}

Email address: {$Nachricht->cMail}
{if !empty($Nachricht->cTel)}Phone: {$Nachricht->cTel}{/if}
{if !empty($Nachricht->cMobil)}Mobile: {$Nachricht->cMobil}{/if}
{if !empty($Nachricht->cFax)}Fax: {$Nachricht->cFax}{/if}

{includeMailTemplate template=footer type=plain}