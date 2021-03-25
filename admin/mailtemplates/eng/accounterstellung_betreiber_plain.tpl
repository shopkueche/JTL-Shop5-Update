{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

As requested we have created an account for you for our online shop at {$ShopURL}.

Please review your account details:

{$Kunde->cVorname} {$Kunde->cNachname}
{$Kunde->cStrasse} {$Kunde->cHausnummer}
{if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}{/if}
{$Kunde->cPLZ} {$Kunde->cOrt}
{if $Kunde->cBundesland}{$Kunde->cBundesland}{/if}
{$Kunde->cLand}
{if $Kunde->cTel}Phone: {$Kunde->cTel}{/if}
{if $Kunde->cMobil}Mobile: {$Kunde->cMobil}{/if}
{if $Kunde->cFax}Fax: {$Kunde->cFax}{/if}
Email address: {$Kunde->cMail}
{if $Kunde->cUSTID}VAT ID: {$Kunde->cUSTID}{/if}

Please use "Forgot password" to set a new password:
{$newPasswordURL|cat:"?email="|cat:$Kunde->cMail}

Using these account details you can log in to your personal account
and track the current status of your order.

We are happy to welcome you as a new customer. If you have any
questions concerning our product portfolio or special items, please do not hesitate to contact us.

We hope you will enjoy exploring our range of products.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template='footer' type='plain'}