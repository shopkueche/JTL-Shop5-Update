{includeMailTemplate template=header type=plain}

Guten Tag,

wir freuen uns, Sie als Newsletter-Abonnent bei {$Firma->cName} begrüßen zu dürfen.

Bitte klicken Sie auf den folgenden Freischalt-Link, um Newsletter zu empfangen:
{$NewsletterEmpfaenger->cFreischaltURL}

Sie können sich ebenso jederzeit vom Newsletter abmelden, indem Sie entweder den Lösch-Link
{$NewsletterEmpfaenger->cLoeschURL}
mit Ihrem Browser aufrufen oder sich im Shop anmelden und den "Newsletter"-Link besuchen.

Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
