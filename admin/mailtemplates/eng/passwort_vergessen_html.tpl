{includeMailTemplate template=header type=html}

We received a request to reset your password. Please click on the following link to do so.<br>
<br>
<a href="{$passwordResetLink}">{$passwordResetLink}</a><br>
<br>
We recommend changing your password regularly to prevent potential misuse. If you did not request a password reset,
please ignore this email.

{includeMailTemplate template=footer type=html}