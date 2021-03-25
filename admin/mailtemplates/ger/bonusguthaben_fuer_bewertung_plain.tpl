{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

vielen Dank für Ihre Bewertung eines Artikels. Ihr Guthabenbonus in Höhe von {$oBewertungGuthabenBonus->fGuthabenBonusLocalized} steht Ihnen ab sofort zur Verfügung.
Sie können Ihr Guthaben jederzeit bei einem Ihrer nächsten Einkäufe einlösen.

Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}