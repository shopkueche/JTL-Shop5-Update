{includeMailTemplate template=header type=plain}

Hallo,
schau dir doch mal meinen Wunschzettel bei {$Firma->cName} an.

{foreach $Wunschliste->CWunschlistePos_arr as $CWunschlistePos}
    *{$CWunschlistePos->cArtikelName}*
    {$ShopURL}/{$CWunschlistePos->Artikel->cURL}
    {foreach $CWunschlistePos->CWunschlistePosEigenschaft_arr as $CWunschlistePosEigenschaft}
        {if $CWunschlistePosEigenschaft->cFreifeldWert}
            {$CWunschlistePosEigenschaft->cEigenschaftName}: {$CWunschlistePosEigenschaft->cFreifeldWert}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}{/if}
        {else}
            {$CWunschlistePosEigenschaft->cEigenschaftName}: {$CWunschlistePosEigenschaft->cEigenschaftWertName}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}{/if}
        {/if}
    {/foreach}
{/foreach}


Alle Artikel anschauen >
{$ShopURL}/index.php?wlid={$Wunschliste->cURLID}

Danke.
{$Kunde->cVorname} {$Kunde->cNachname}

{includeMailTemplate template=footer type=plain}
