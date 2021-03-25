{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

As of today, we have closed your account {$Kunde->cMail} as requested.

If you were not satisfied with our services, we would be grateful to receive your feedback so that we can improve them in the future.

In case you want to purchase from us again at a later time, just register again and create a new account with us.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}