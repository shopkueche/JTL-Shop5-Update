{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
We are happy to inform you that a voucher has been deposited for you in your account.<br>
<br>
<strong>Value of voucher:</strong> {$Gutschein->cLocalizedWert}<br>
<br>
Reason for issuing the voucher: {$Gutschein->cGrund}<br>
<br>
The voucher is valid for your next order. The voucher value is then subtracted from your purchase value.<br>
<br>
Enjoy your next purchase in our shop.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}