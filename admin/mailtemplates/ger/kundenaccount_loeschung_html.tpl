{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
wie von Ihnen gewünscht haben wir heute Ihr Kundenkonto mit der
E-Mail-Adresse {$Kunde->cMail} gelöscht.<br>
<br>
Sollten Sie mit unserem Service nicht zufrieden gewesen sein,
teilen Sie uns dies bitte mit, damit wir unseren Service verbessern
können.<br>
<br>
Falls Sie zu einem späteren Zeitpunkt wieder bei uns einkaufen
möchten, melden Sie sich einfach erneut an und eröffnen Sie ein neues
Kundenkonto bei uns.<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}