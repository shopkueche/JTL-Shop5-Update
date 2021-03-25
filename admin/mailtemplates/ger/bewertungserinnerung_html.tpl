{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
möchten Sie Ihre Erfahrungen mit Ihren kürzlich bei uns erworbenen Produkten mit anderen teilen, so würden wir uns sehr freuen, wenn Sie eine Bewertung abgeben.<br>
<br>
Zur Abgabe einer Bewertung klicken Sie einfach auf eines Ihrer erworbenen Produkte:<br>
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
Vielen Dank für Ihre Mühe.<br>
<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
