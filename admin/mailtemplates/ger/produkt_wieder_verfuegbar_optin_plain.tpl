{includeMailTemplate template=header type=plain}

Guten Tag,

bitte fügen Sie den folgenden Freischalt-Link
{$Optin->activationURL}
in Ihren Browser ein, wenn Sie von uns informiert werden möchten, sobald der Artikel
"{$Artikel->cName}" wieder verfügbar ist.

Wenn Sie sich von dieser Benachrichtigungsfunktion abmelden möchten,
öffnen Sie bitte den folgenden Link in Ihrem Browser:
{$Optin->deactivationURL}


Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
