{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname}  {$Kunde->cNachname},

We are always greatful for feedback on recently bought products. We would love for you to leave a rating!

Please click on the item to rate it:

{foreach $Bestellung->Positionen as $Position}
    {if $Position->nPosTyp == 1}
        {$Position->cName} ({$Position->cArtNr})
        {$ShopURL}/index.php?a={$Position->kArtikel}&bewertung_anzeigen=1#tab-votes

        {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}

            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
        {/foreach}
    {/if}
{/foreach}

Thanks for sharing your feedback!


Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}
