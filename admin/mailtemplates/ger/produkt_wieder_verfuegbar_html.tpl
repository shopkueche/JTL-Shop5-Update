{includeMailTemplate template=header type=html}

Guten Tag,<br>
<br>
wir freuen uns, Ihnen mitteilen zu dürfen, dass der Artikel {$Artikel->cName} ab sofort wieder bei uns erhältlich ist.<br>
<br>
Über diesen Link kommen Sie direkt zum Artikel in unserem Onlineshop: <a href="{$ShopURL}/{$Artikel->cURL}">{$Artikel->cName}</a><br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
