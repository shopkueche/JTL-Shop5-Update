{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
the tracking status for order no. {$Bestellung->cBestellNr} has changed.<br>
<br>
{foreach $Bestellung->oLieferschein_arr as $oLieferschein}
    {if !$oLieferschein->getEmailVerschickt()}
        <table cellpadding="10" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
            <tr>
                <td width="10%" align="left" valign="top">
                    Quantity
                </td>
                <td align="left" valign="top">
                    Position
                </td>
            </tr>
            {foreach $oLieferschein->oPosition_arr as $Position}
                <tr>
                    <td align="left" valign="top">
                        {$Position->nAusgeliefert}
                    </td>
                    <td align="left" valign="top">
                        {if $Position->nPosTyp == 1}
                            <strong>{$Position->cName}</strong> {if $Position->cArtNr}({$Position->cArtNr}){/if}
                            {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}
                                <br>{$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
                            {/foreach}

                            {* Seriennummer *}
                            {if $Position->cSeriennummer|strlen > 0}
                                <br>Serial number: {$Position->cSeriennummer}
                            {/if}

                            {* MHD *}
                            {if $Position->dMHD|strlen > 0}
                                <br>Shelf life expiration date: {$Position->dMHD_de}
                            {/if}

                            {* Charge *}
                            {if $Position->cChargeNr|strlen > 0}
                                <br>Batch: {$Position->cChargeNr}
                            {/if}
                        {else}
                            <strong>{$Position->cName}</strong>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
        {foreach $oLieferschein->oVersand_arr as $oVersand}
            {if $oVersand->getIdentCode()|strlen > 0}
                <br><strong>Tracking Url:</strong> <a href="{$oVersand->getLogistikVarUrl()}">{$oVersand->getIdentCode()}</a>
            {/if}
        {/foreach}
    {/if}
{/foreach}<br>
<br>
You will be notified of the status of your order separately.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}
