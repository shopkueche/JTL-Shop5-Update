{includeMailTemplate template=header type=html}

Hello,<br>
<br>
we're pleased to welcome you as a new newsletter subscriber at {$Firma->cName}.<br>
<br>
Please click the activation code below to receive your newsletter:<br>
<a href="{$NewsletterEmpfaenger->cFreischaltURL}">{$NewsletterEmpfaenger->cFreischaltURL}</a><br>
<br>
You can unsubscribe the newsletter at any time either by clicking the unsubscribe link<br>
<a href="{$NewsletterEmpfaenger->cLoeschURL}">{$NewsletterEmpfaenger->cLoeschURL}</a>
or by login into the shop and following the "Newsletter" link.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}
