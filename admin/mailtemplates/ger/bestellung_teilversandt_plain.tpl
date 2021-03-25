{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

der Versandstatus Ihrer Bestellung mit Bestellnummer {$Bestellung->cBestellNr} hat sich geändert.

{foreach $Bestellung->oLieferschein_arr as $oLieferschein}
    {if !$oLieferschein->getEmailVerschickt()}
        {foreach $oLieferschein->oPosition_arr as $Position}
            {$Position->nAusgeliefert} x {if $Position->nPosTyp == 1}{$Position->cName} {if $Position->cArtNr}({$Position->cArtNr}){/if}
            {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}
                {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
            {/foreach}
            {if $Position->cSeriennummer|strlen > 0}
                Seriennummer: {$Position->cSeriennummer}
            {/if}
            {if $Position->dMHD|strlen > 0}
                Mindesthaltbarkeitsdatum: {$Position->dMHD_de}
            {/if}
            {if $Position->cChargeNr|strlen > 0}
                Charge: {$Position->cChargeNr}
            {/if}
        {else}
            {$Position->cName}
        {/if}
        {/foreach}

        {foreach $oLieferschein->oVersand_arr as $oVersand}
            {if $oVersand->getIdentCode()|strlen > 0}
                Tracking-URL: {$oVersand->getLogistikVarUrl()}
            {/if}
        {/foreach}
    {/if}
{/foreach}

Über den weiteren Verlauf Ihrer Bestellung werden wir Sie jeweils gesondert informieren.

Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
