{includeMailTemplate template=header type=html}

Hello,<br>
Take a look at my wish list at {$Firma->cName}.<br>
<br>
<table cellpadding="5" cellspacing="0" border="0" width="100%">
    <tr>
        {foreach $Wunschliste->CWunschlistePos_arr as $CWunschlistePos}
            <td class="column" align="left" valign="top" bgcolor="#ffffff">
                <a href="{$ShopURL}/{$CWunschlistePos->Artikel->cURL}">
                    <img src="{$ShopURL}/{$CWunschlistePos->Artikel->Bilder[0]->cPfadKlein}" style="border: 1px solid #bebcb7">
                </a><br>
                <br>
                <a href="{$ShopURL}/{$CWunschlistePos->Artikel->cURL}" style="color: #1E7EC8;">{$CWunschlistePos->cArtikelName}</a><br>
                {foreach $CWunschlistePos->CWunschlistePosEigenschaft_arr as $CWunschlistePosEigenschaft}
                    {if $CWunschlistePosEigenschaft->cFreifeldWert}
                        <strong>{$CWunschlistePosEigenschaft->cEigenschaftName}:<strong>{$CWunschlistePosEigenschaft->cFreifeldWert}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}{/if}
                    {else}
                        <strong>{$CWunschlistePosEigenschaft->cEigenschaftName}:</strong> {$CWunschlistePosEigenschaft->cEigenschaftWertName}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}{/if}
                    {/if}
                {/foreach}
            </td>
            {if $CWunschlistePos@iteration % 2 === 0}</tr>{if $CWunschlistePos@iteration !== 1}<tr>{/if}{/if}
        {/foreach}
    </tr>
</table><br>
<a href="{$ShopURL}/index.php?wlid={$Wunschliste->cURLID}">View all products</a><br>
<br>
Kind regards,<br>
<strong>{$Kunde->cVorname} {$Kunde->cNachname}</strong>

{includeMailTemplate template=footer type=html}
