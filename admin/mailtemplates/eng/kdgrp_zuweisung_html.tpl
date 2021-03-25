{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
You are now part of the customer group: {$Kundengruppe->cName} in our online shop <a href="{$URL_SHOP}">{$Einstellungen.global.global_shopname}</a> and are therefore entitled to different price conditions {if $Kundengruppe->fRabatt>0}(for example {$Kundengruppe->fRabatt|replace:".":","}% global discount){/if}.<br>
<br>
In case you do not see the new prices, please log off and on again.<br>
<br>
Yours sincerely,<br>
<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}