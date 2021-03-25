{includeMailTemplate template=header type=plain}

Dear customer,

We are happy to inform you that the item {$Artikel->cName} is once again available in our online shop.

Link to item: {$ShopURL}/{$Artikel->cURL}

Yours sincerely,
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=plain}
