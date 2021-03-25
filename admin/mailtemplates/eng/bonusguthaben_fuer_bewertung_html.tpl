{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
Thank you for your product rating. Your bonus credit of {$oBewertungGuthabenBonus->fGuthabenBonusLocalized} is valid for any of your future purchases.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}