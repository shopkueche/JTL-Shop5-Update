{includeMailTemplate template=header type=plain}

Hello {$Nachricht->cName},

I would like to recommend a product to you.

Please take a look: {$Artikel->cName} - {$ShopURL}/{$Artikel->cURL}

Thank you!

Yours sincerely,
{$VonKunde->cVorname} {$VonKunde->cNachname}

{includeMailTemplate template=footer type=plain}