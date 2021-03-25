{includeMailTemplate template=header type=html}

Guten Tag,<br>
<br>
wir freuen uns, Sie als Newsletter-Abonnent bei {$Firma->cName} begrüßen zu können.<br>
<br>
Bitte klicken Sie auf den folgenden Freischalt-Link, um Newsletter zu empfangen:<br>
<a href="{$NewsletterEmpfaenger->cFreischaltURL}">{$NewsletterEmpfaenger->cFreischaltURL}</a><br>
<br>
Sie können sich ebenso jederzeit vom Newsletter abmelden, indem Sie entweder auf den Lösch-Link klicken:<br>
<a href="{$NewsletterEmpfaenger->cLoeschURL}">{$NewsletterEmpfaenger->cLoeschURL}</a><br>
oder sich im Shop anmelden und den "Newsletter"-Link besuchen.<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
