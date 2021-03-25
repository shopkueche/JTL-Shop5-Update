{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
As of today, we have closed your account {$Kunde->cMail} as requested.<br>
<br>
If you were not satisfied with our services, we would be grateful to receive your feedback so that we can improve them in the future.<br>
<br>
In case you want to purchase from us again at a later time, just register again and create a new account with us.<br>
<br>
Yours sincerely,<br>
<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}