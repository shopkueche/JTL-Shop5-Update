{includeMailTemplate template=header type=html}

Dear Customer,<br>
<br>
Please copy the following confirmation-Link into your browser
if you would like to be notified when the item<br>
<b>{$Artikel->cName}</b><br>
becomes available again: <a href="{$Optin->activationURL}">{$Optin->activationURL}</a><br>
<br>
If you want to unsubscribe from this notification feature, please insert the following link into your browser:<br>
<a href="{$Optin->deactivationURL}">{$Optin->deactivationURL}<a><br>
<br>
<br>
Yours sincerely,<br>
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=html}
