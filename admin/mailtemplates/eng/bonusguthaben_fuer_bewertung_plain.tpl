{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

Thank you for your product rating. Your bonus credit of {$oBewertungGuthabenBonus->fGuthabenBonusLocalized} is valid for any of your future purchases.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}