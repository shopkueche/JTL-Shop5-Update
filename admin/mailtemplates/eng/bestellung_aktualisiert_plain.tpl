{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

Your order at {$Einstellungen.global.global_shopname} has been updated.

Your order with the order number {$Bestellung->cBestellNr} consists of the following items:

{foreach $Bestellung->Positionen as $Position}

    {if $Position->nPosTyp == 1}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen === 'Y' && $Position->cLieferstatus}

        Delivery time: {$Position->cLieferstatus}{/if}
        {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}

            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}{/foreach}
        {if $Position->cSeriennummer|strlen > 0}
            Serialnumber: {$Position->cSeriennummer}
        {/if}
        {if $Position->dMHD|strlen > 0}
            Shelf life expiration date: {$Position->dMHD}
        {/if}
        {if $Position->cChargeNr|strlen > 0}
            Batch: {$Position->cChargeNr}
        {/if}
    {else}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{/if}
{/foreach}

{if $Einstellungen.global.global_steuerpos_anzeigen !== 'N'}{foreach $Bestellung->Steuerpositionen as $Steuerposition}
    {$Steuerposition->cName}: {$Steuerposition->cPreisLocalized}
{/foreach}{/if}
{if isset($Bestellung->GuthabenNutzen) && $Bestellung->GuthabenNutzen == 1}
    Voucher: -{$Bestellung->GutscheinLocalized}
{/if}

Total: {$Bestellung->WarensummeLocalized[0]}


Your billing address:

{$Kunde->cVorname} {$Kunde->cNachname}
{$Kunde->cStrasse} {$Kunde->cHausnummer}
{if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}
{/if}{$Kunde->cPLZ} {$Kunde->cOrt}
{if $Kunde->cBundesland}{$Kunde->cBundesland}
{/if}{$Kunde->cLand}
{if $Kunde->cTel}Phone: {$Kunde->cTel}
{/if}{if $Kunde->cMobil}Mobile: {$Kunde->cMobil}
{/if}{if $Kunde->cFax}Fax: {$Kunde->cFax}
{/if}
Email: {$Kunde->cMail}
{if $Kunde->cUSTID}VAT ID: {$Kunde->cUSTID}
{/if}

{if $Bestellung->Lieferadresse->kLieferadresse>0}
    Your delivery address:

    {$Bestellung->Lieferadresse->cVorname} {$Bestellung->Lieferadresse->cNachname}
    {$Bestellung->Lieferadresse->cStrasse} {$Bestellung->Lieferadresse->cHausnummer}
    {if $Bestellung->Lieferadresse->cAdressZusatz}{$Bestellung->Lieferadresse->cAdressZusatz}
    {/if}{$Bestellung->Lieferadresse->cPLZ} {$Bestellung->Lieferadresse->cOrt}
    {if $Bestellung->Lieferadresse->cBundesland}{$Bestellung->Lieferadresse->cBundesland}
    {/if}{$Bestellung->Lieferadresse->cLand}
    {if $Bestellung->Lieferadresse->cTel}Tel: {$Bestellung->Lieferadresse->cTel}
    {/if}{if $Bestellung->Lieferadresse->cMobil}Mobil: {$Bestellung->Lieferadresse->cMobil}
{/if}{if $Bestellung->Lieferadresse->cFax}Fax: {$Bestellung->Lieferadresse->cFax}
{/if}{if $Bestellung->Lieferadresse->cMail}Email: {$Bestellung->Lieferadresse->cMail}
{/if}
{else}
    Delivery address same as billing address.
{/if}

You have chosen the following payment option: {$Bestellung->cZahlungsartName}

{if isset($Zahlungsart->cHinweisText) && $Zahlungsart->cHinweisText|strlen > 0} {$Zahlungsart->cHinweisText}


{/if}

{if $Bestellung->Zahlungsart->cModulId === 'za_rechnung_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_lastschrift_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_barzahlung_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_paypal_jtl'}
{/if}

You will be notified of the status of your order separately.


Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}
