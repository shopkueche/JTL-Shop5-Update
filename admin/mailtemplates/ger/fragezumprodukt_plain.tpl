{includeMailTemplate template=header type=plain}

Shop: {$Einstellungen.global.global_shopname}

Frage zu folgendem Produkt: {$Artikel->cName}

E-Mail-Adresse des Kunden: {$Nachricht->cMail}

Frage: {$Nachricht->cNachricht}

{if !empty($Nachricht->cVorname) || !empty($Nachricht->cNachname) || !empty($Nachricht->cFirma)}
    Anfrage von:
    {if !empty($Nachricht->cVorname)}{$Nachricht->cVorname} {/if}
    {if !empty($Nachricht->cNachname)}{$Nachricht->cNachname}{/if}
    {if !empty($Nachricht->cFirma)}{$Nachricht->cFirma}{/if}
{/if}

E-Mail-Adresse: {$Nachricht->cMail}
{if !empty($Nachricht->cTel)}Tel.: {$Nachricht->cTel}{/if}
{if !empty($Nachricht->cMobil)}Mobil: {$Nachricht->cMobil}{/if}
{if !empty($Nachricht->cFax)}Fax: {$Nachricht->cFax}{/if}

{includeMailTemplate template=footer type=plain}