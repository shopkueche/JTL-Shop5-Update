{includeMailTemplate template=header type=plain}

Dear Customer,

Please copy the following confirmation-Link into your browser
if you would like to be notified then the item
"{$Artikel->cName}"
becomes available again: {$Optin->activationURL}

If you want to unsubscribe from this notification feature,
please insert the following link into your browser:
{$Optin->deactivationURL}

Yours sincerely,
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=plain}
