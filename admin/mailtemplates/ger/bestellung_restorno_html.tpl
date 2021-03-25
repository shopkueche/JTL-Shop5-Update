{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
die Stornierung Ihrer Bestellung bei {$Einstellungen.global.global_shopname} wurde soeben aufgehoben.<br>
<strong>Bestellnummer:</strong> {$Bestellung->cBestellNr}<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}