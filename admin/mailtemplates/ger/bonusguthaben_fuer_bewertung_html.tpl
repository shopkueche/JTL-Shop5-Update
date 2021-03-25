{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
vielen Dank für Ihre Bewertung eines Artikels. Ihr Guthabenbonus in Höhe von {$oBewertungGuthabenBonus->fGuthabenBonusLocalized} steht Ihnen ab sofort zur Verfügung.<br>
Sie können Ihr Guthaben jederzeit bei einem Ihrer nächsten Einkäufe einlösen.<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}