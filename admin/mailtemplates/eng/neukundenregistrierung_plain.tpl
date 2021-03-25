{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

Thank you very much for registering for our online shop under {$ShopURL}.

Please review your account details:

{$Kunde->cVorname} {$Kunde->cNachname}
{$Kunde->cStrasse} {$Kunde->cHausnummer}
{if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}
{/if}{$Kunde->cPLZ} {$Kunde->cOrt}
{if $Kunde->cBundesland}{$Kunde->cBundesland}
{/if}{$Kunde->cLand}
{if $Kunde->cTel}Phone: {$Kunde->cTel}
{/if}{if $Kunde->cMobil}Mobile: {$Kunde->cMobil}
{/if}{if $Kunde->cFax}Fax: {$Kunde->cFax}
{/if}
Email address: {$Kunde->cMail}
{if $Kunde->cUSTID}VAT ID: {$Kunde->cUSTID}
{/if}

Using these account details you can log into your personal account and track the current status of your order.

We are happy to welcome you as a new customer. In case you have any questions about our product portfolio or special products, please do not hesitate to contact us.

We hope you will enjoy exploring our range of products.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}