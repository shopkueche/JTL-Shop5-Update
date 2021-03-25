{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

die Stornierung Ihrer Bestellung bei {$Einstellungen.global.global_shopname} wurde soeben aufgehoben.
Bestellnummer: {$Bestellung->cBestellNr}

Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}