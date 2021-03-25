{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

möchten Sie Ihre Erfahrungen mit Ihren kürzlich bei uns erworbenen Produkten mit anderen teilen, so würden wir uns sehr freuen, wenn Sie eine Bewertung abgeben.

Zur Abgabe einer Bewertung klicken Sie einfach auf eines Ihrer erworbenen Produkte:

{foreach $Bestellung->Positionen as $Position}
    {if $Position->nPosTyp == 1}
        {$Position->cName} ({$Position->cArtNr})
        {$ShopURL}/index.php?a={$Position->kArtikel}&bewertung_anzeigen=1#tab-votes

        {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}

            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
        {/foreach}
    {/if}
{/foreach}

Vielen Dank für Ihre Mühe.


Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
