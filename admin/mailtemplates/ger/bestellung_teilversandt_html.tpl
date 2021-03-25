{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
der Versandstatus Ihrer Bestellung mit Bestellnummer {$Bestellung->cBestellNr} hat sich geändert.<br>
<br>
{foreach $Bestellung->oLieferschein_arr as $oLieferschein}
    {if !$oLieferschein->getEmailVerschickt()}
        <table cellpadding="10" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
            <tr>
                <td width="10%" align="left" valign="top">
                    Anzahl
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
                                <br>Seriennummer: {$Position->cSeriennummer}
                            {/if}

                            {* MHD *}
                            {if $Position->dMHD|strlen > 0}
                                <br>Mindesthaltbarkeitsdatum: {$Position->dMHD_de}
                            {/if}

                            {* Charge *}
                            {if $Position->cChargeNr|strlen > 0}
                                <br>Charge: {$Position->cChargeNr}
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
                <br><strong>Tracking-URL:</strong> <a href="{$oVersand->getLogistikVarUrl()}">{$oVersand->getIdentCode()}</a>
            {/if}
        {/foreach}
    {/if}
{/foreach}<br>
<br>
Über den weiteren Verlauf Ihrer Bestellung werden wir Sie jeweils gesondert informieren.<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
