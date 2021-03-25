{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

we have received your payment of {$Bestellung->WarensummeLocalized[0]} for your order of {$Bestellung->dErstelldatum_en}.

Your order is as follows:

{foreach $Bestellung->Positionen as $Position}
    {if $Position->nPosTyp == 1}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}
        {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}
            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
        {/foreach}
    {else}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}
    {/if}
{/foreach}

{foreach $Bestellung->Steuerpositionen as $Steuerposition}
    {$Steuerposition->cName}: {$Steuerposition->cPreisLocalized}
{/foreach}

{if isset($GuthabenNutzen) && $GuthabenNutzen == 1}
    Voucher: -{$GutscheinLocalized}
{/if}

Total: {$Bestellung->WarensummeLocalized[0]}

You will be notified of the dispatch of your goods separately.

Yours sincerely,

{$Firma->cName}

{includeMailTemplate template=footer type=plain}
