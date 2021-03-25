{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

die Zahlung für Ihre Bestellung mit Bestellnummer {$Bestellung->cBestellNr} vom {$Bestellung->dErstelldatum_de} in Höhe von {$Bestellung->WarensummeLocalized[0]} ist per {$Bestellung->Zahlungsart->cName} bei uns eingegangen.

Nachfolgend erhalten Sie nochmals einen Überblick über Ihre Bestellung:

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
    Gutschein: -{$GutscheinLocalized}
{/if}

Gesamtsumme: {$Bestellung->WarensummeLocalized[0]}


Über den Versand der Ware werden wir Sie gesondert informieren.

Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
