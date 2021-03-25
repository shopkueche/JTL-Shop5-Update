{includeMailTemplate template=header type=html}

Hallo {$Nachricht->cName},<br>
<br>
anbei möchte ich dir gern den folgenden Artikel empfehlen:<br>
<br>
Schau ihn dir doch mal an: <a href="{$ShopURL}/{$Artikel->cURL}">{$Artikel->cName}</a><br>
<br>
Vielen Dank.<br>
<br>
Mit freundlichem Gruß<br>
{$VonKunde->cVorname} {$VonKunde->cNachname}

{includeMailTemplate template=footer type=html}