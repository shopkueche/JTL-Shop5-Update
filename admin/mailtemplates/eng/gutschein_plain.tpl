{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

We are happy to inform you that a voucher has been deposited for you in your account.

Value of voucher: {$Gutschein->cLocalizedWert}

Reason for issuing the voucher: {$Gutschein->cGrund}

The voucher is valid for your next order. The voucher value is then subtracted from your purchase value.

Enjoy your next purchase in our shop.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}