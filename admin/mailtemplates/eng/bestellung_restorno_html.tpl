{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
the cancellation of your order at {$Einstellungen.global.global_shopname} has been reversed.<br>
<strong>Order number:</strong> {$Bestellung->cBestellNr}<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}