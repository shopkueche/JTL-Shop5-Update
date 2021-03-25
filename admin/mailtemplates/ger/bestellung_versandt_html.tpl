{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
Ihre Bestellung vom {$Bestellung->dErstelldatum_de} mit Bestellnummer {$Bestellung->cBestellNr} wurde heute an Sie versandt.<br>
<br>
{foreach $Bestellung->oLieferschein_arr as $oLieferschein}
    {if $oLieferschein->oVersand_arr|count > 1}
        Mit den nachfolgenden Links können Sie sich über den Status Ihrer Sendungen informieren:
    {else}
        Mit dem nachfolgenden Link können Sie sich über den Status Ihrer Sendung informieren:
    {/if}<br>
    <br>
    {foreach $oLieferschein->oVersand_arr as $oVersand}
        {if $oVersand->getIdentCode()|strlen > 0}
            <strong>Tracking-URL:</strong> <a href="{$oVersand->getLogistikVarUrl()}">{$oVersand->getIdentCode()}</a><br>
            {if $oVersand->getHinweis()|strlen > 0}
                <strong>Tracking-Hinweis:</strong> {$oVersand->getHinweis()}<br>
            {/if}
        {/if}
    {/foreach}
{/foreach}
<br>
Wir wünschen Ihnen viel Spaß mit der Ware und bedanken uns für Ihren Einkauf und Ihr Vertrauen.<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
