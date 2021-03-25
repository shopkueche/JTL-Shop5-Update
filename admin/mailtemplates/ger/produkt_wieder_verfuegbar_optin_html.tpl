{includeMailTemplate template=header type=html}

Guten Tag,<br>
<br>
bitte fügen Sie den folgenden Freischalt-Link<br>
<a href="{$Optin->activationURL}">{$Optin->activationURL}</a>,<br>
<br>
in Ihren Browser ein, wenn Sie von uns informiert werden möchten, sobald der Artikel<br>
<b>{$Artikel->cName}</b><br>
wieder verfügbar ist.<br>
<br>
Wenn Sie sich von dieser Benachrichtigungsfunktion abmelden möchten,<br>
öffnen Sie bitte den folgenden Link in Ihrem Browser:<br>
<a href="{$Optin->deactivationURL}">{$Optin->deactivationURL}</a>,<br>
<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
