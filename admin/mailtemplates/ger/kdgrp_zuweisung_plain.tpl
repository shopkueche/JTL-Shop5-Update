{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

wir haben Ihre Kundengruppe geändert. Ab sofort sollten Ihnen andere Preise angezeigt werden.

Ist dies nicht der Fall, melden Sie sich bitte ab und loggen sich dann erneut ein.

Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}