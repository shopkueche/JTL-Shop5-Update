{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

wie von Ihnen gewünscht haben wir heute Ihr Kundenkonto mit der
E-Mail-Adresse {$Kunde->cMail} gelöscht.

Sollten Sie mit unserem Service nicht zufrieden gewesen sein,
teilen Sie uns dies bitte mit, damit wir unseren Service verbessern
können.

Falls Sie zu einem späteren Zeitpunkt wieder bei uns einkaufen
möchten, melden Sie sich einfach erneut an und eröffnen Sie ein neues
Kundenkonto bei uns.

Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}