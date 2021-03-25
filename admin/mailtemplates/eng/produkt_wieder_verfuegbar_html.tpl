{includeMailTemplate template=header type=html}

Dear customer,<br>
<br>
We are happy to inform you that the item  {$Artikel->cName} is once again available in our online shop.<br>
<br>
Link to item: <a href="{$ShopURL}/{$Artikel->cURL}">{$ShopURL}/{$Artikel->cURL}</a><br>
<br>
Yours sincerely,<br>
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=html}
