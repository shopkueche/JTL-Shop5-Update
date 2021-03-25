{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

You are now part of the customer group {$Kundengruppe->cName} in our online shop {$Einstellungen.global.global_shopname} ({$URL_SHOP}) and are therefore entitled to different price conditions {if $Kundengruppe->fRabatt>0}(for example {$Kundengruppe->fRabatt|replace:".":","}% global discount){/if}.

In case you do not see the new prices, please log off and on again.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}