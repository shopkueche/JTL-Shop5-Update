{includeMailTemplate template=header type=plain}

Nachricht:
{$Nachricht->cNachricht}

Anfrage von:
{if $Nachricht->cVorname}{$Nachricht->cVorname} {/if}{if $Nachricht->cNachname}{$Nachricht->cNachname}{/if}
{if $Nachricht->cFirma}{$Nachricht->cFirma}{/if}

E-Mail-Adresse: {$Nachricht->cMail}
{if $Nachricht->cTel}Tel.: {$Nachricht->cTel}{/if}
{if $Nachricht->cMobil}Mobil: {$Nachricht->cMobil}{/if}
{if $Nachricht->cFax}Fax: {$Nachricht->cFax}{/if}

{includeMailTemplate template=footer type=plain}
