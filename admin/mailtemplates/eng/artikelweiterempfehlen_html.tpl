{includeMailTemplate template=header type=html}

Hello {$Nachricht->cName},<br>
<br>
I would like to recommend a product to you.<br>
<br>
Please take a look: <a href="{$ShopURL}/{$Artikel->cURL}">{$Artikel->cName}</a><br>
<br>
Thank you!<br>
<br>
Yours sincerely,<br>
{$VonKunde->cVorname} {$VonKunde->cNachname}

{includeMailTemplate template=footer type=html}