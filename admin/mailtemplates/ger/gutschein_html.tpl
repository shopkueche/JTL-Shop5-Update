{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
wir freuen uns, Ihnen mitteilen zu dürfen, dass auf Ihrem Kundenkonto ein Gutschein für Sie hinterlegt wurde.<br>
<br>
<strong>Gutscheinwert:</strong> {$Gutschein->cLocalizedWert}<br>
<br>
Grund für die Ausstellung des Gutscheins: {$Gutschein->cGrund}<br>
<br>
Diesen Gutschein können Sie einfach bei Ihrer nächsten Bestellung einlösen. Der Betrag wird dann von Ihrem Einkaufswert abgezogen.<br>
<br>
Viel Spaß bei Ihrem nächsten Einkauf in unserem Shop.<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}