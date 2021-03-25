{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
We are always greatful for feedback on recently bought products. We would love for you to leave a rating!<br>
<br>
Please click on the item to rate it:<br>
<br>
{foreach $Bestellung->Positionen as $Position}
    <table cellpadding="00" cellspacing="0" border="0" width="100%">
        <tr>
            <td valign="top" style="padding-bottom:5px;">
                {if $Position->nPosTyp == 1}
                    <a href="{$ShopURL}/index.php?a={$Position->kArtikel}&bewertung_anzeigen=1#tab-votes"><strong>{$Position->cName}</strong> ({$Position->cArtNr})</a>
                    {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}
                        <br><strong>{$WKPosEigenschaft->cEigenschaftName}</strong>: {$WKPosEigenschaft->cEigenschaftWertName}
                    {/foreach}
                {/if}
            </td>
        </tr>
    </table>
{/foreach}<br>
<br>
Thanks for sharing your feedback!<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}
