{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
wir haben Ihre Kundengruppe geändert. Ab sofort sollten Ihnen andere Preise angezeigt werden.<br>
<br>
Ist dies nicht der Fall, melden Sie sich bitte ab und loggen sich dann erneut ein.<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}