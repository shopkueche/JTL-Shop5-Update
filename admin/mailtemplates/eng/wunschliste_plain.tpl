{includeMailTemplate template=header type=plain}

Hello,
Take a look at my wish list at {$Firma->cName}.

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

View all products
{$ShopURL}/index.php?wlid={$Wunschliste->cURLID}

Kind regards,
{$Kunde->cVorname} {$Kunde->cNachname}

{includeMailTemplate template=footer type=plain}
