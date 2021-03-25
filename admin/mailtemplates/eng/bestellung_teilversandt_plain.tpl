{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

the tracking status for order no. {$Bestellung->cBestellNr} has changed.

{foreach $Bestellung->oLieferschein_arr as $oLieferschein}
    {if !$oLieferschein->getEmailVerschickt()}
        {foreach $oLieferschein->oPosition_arr as $Position}
            {$Position->nAusgeliefert} x {if $Position->nPosTyp == 1}{$Position->cName} {if $Position->cArtNr}({$Position->cArtNr}){/if}
            {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}
                {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
            {/foreach}
            {if $Position->cSeriennummer|strlen > 0}
                Serial number: {$Position->cSeriennummer}
            {/if}
            {if $Position->dMHD|strlen > 0}
                shelf life expiration date: {$Position->dMHD}
            {/if}
            {if $Position->cChargeNr|strlen > 0}
                Batch: {$Position->cChargeNr}
            {/if}
        {else}
            {$Position->cName}
        {/if}
        {/foreach}

        {foreach $oLieferschein->oVersand_arr as $oVersand}
            {if $oVersand->getIdentCode()|strlen > 0}
                Tracking URL: {$oVersand->getLogistikVarUrl()}
            {/if}
        {/foreach}
    {/if}
{/foreach}

You will be notified of the status of your order separately.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}
