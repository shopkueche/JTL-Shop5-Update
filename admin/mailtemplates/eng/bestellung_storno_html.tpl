{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
your order at {$Einstellungen.global.global_shopname} has been cancelled.
<strong>Order number:</strong> {$Bestellung->cBestellNr}<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}