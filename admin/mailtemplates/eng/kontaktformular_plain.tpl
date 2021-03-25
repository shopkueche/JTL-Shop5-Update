{includeMailTemplate template=header type=plain}

Message:
{$Nachricht->cNachricht}

Contact person:
{if $Nachricht->cVorname}{$Nachricht->cVorname} {/if}{if $Nachricht->cNachname}{$Nachricht->cNachname}{/if}
{if $Nachricht->cFirma}{$Nachricht->cFirma}{/if}

Email address: {$Nachricht->cMail}
{if $Nachricht->cTel}Phone: {$Nachricht->cTel}{/if}
{if $Nachricht->cMobil}Mobile: {$Nachricht->cMobil}{/if}
{if $Nachricht->cFax}Fax: {$Nachricht->cFax}{/if}

{includeMailTemplate template=footer type=plain}