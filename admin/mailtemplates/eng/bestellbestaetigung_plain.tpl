{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

Thank you for your order at {$Einstellungen.global.global_shopname}.

{if $Verfuegbarkeit_arr.cArtikelName_arr|@count > 0}
    {$Verfuegbarkeit_arr.cHinweis}
    {foreach $Verfuegbarkeit_arr.cArtikelName_arr as $cArtikelname}
        {$cArtikelname}

    {/foreach}
{/if}

Your order with the order number {$Bestellung->cBestellNr} consists of the following items:

{foreach $Bestellung->Positionen as $Position}
    {if $Position->nPosTyp == 1}
        {if !empty($Position->kKonfigitem)} * {/if}{$Position->nAnzahl}x {$Position->cName} {if $Position->cArtNr}({$Position->cArtNr}){/if} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen === 'Y' && $Position->cLieferstatus}

        Delivery time: {$Position->cLieferstatus}{/if}
        {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}

            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}{/foreach}
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

Delivery time: {if isset($Bestellung->cEstimatedDeliveryEx)}{$Bestellung->cEstimatedDeliveryEx}{else}{$Bestellung->cEstimatedDelivery}{/if}


Your billing address:

{if !empty($Kunde->cFirma)}{$Kunde->cFirma} - {if !empty($Kunde->cZusatz)}{$Kunde->cZusatz}{/if}{/if}
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
Email address: {$Kunde->cMail}
{if $Kunde->cUSTID}VAT ID: {$Kunde->cUSTID}
{/if}

{if !empty($Bestellung->Lieferadresse->kLieferadresse)}
    Your delivery address:

    {if !empty($Bestellung->Lieferadresse->cFirma)}{$Bestellung->Lieferadresse->cFirma}{/if}
    {$Bestellung->Lieferadresse->cVorname} {$Bestellung->Lieferadresse->cNachname}
    {$Bestellung->Lieferadresse->cStrasse} {$Bestellung->Lieferadresse->cHausnummer}
    {if $Bestellung->Lieferadresse->cAdressZusatz}{$Bestellung->Lieferadresse->cAdressZusatz}
    {/if}{$Bestellung->Lieferadresse->cPLZ} {$Bestellung->Lieferadresse->cOrt}
    {if $Bestellung->Lieferadresse->cBundesland}{$Bestellung->Lieferadresse->cBundesland}
    {/if}{$Bestellung->Lieferadresse->cLand}
    {if $Bestellung->Lieferadresse->cTel}Phone: {$Bestellung->Lieferadresse->cTel}
    {/if}{if $Bestellung->Lieferadresse->cMobil}Mobile: {$Bestellung->Lieferadresse->cMobil}
{/if}{if $Bestellung->Lieferadresse->cFax}Fax: {$Bestellung->Lieferadresse->cFax}
{/if}{if $Bestellung->Lieferadresse->cMail}Email address: {$Bestellung->Lieferadresse->cMail}
{/if}
{else}
    Delivery address same as billing address.
{/if}

You have chosen the following payment option: {$Bestellung->cZahlungsartName}

{if $Bestellung->Zahlungsart->cModulId === 'za_ueberweisung_jtl'}
    Please make the following cash transfer:
    Account owner:{$Firma->cKontoinhaber}
    Bank name:{$Firma->cBank}
    IBAN:{$Firma->cIBAN}
    BIC:{$Firma->cBIC}

    Reference:{$Bestellung->cBestellNr}
    Total:{$Bestellung->WarensummeLocalized[0]}


{elseif $Bestellung->Zahlungsart->cModulId === 'za_nachnahme_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_kreditkarte_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_rechnung_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_lastschrift_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_barzahlung_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_paypal_jtl'}
{/if}

{if isset($Zahlungsart->cHinweisText) && $Zahlungsart->cHinweisText|strlen > 0}  {$Zahlungsart->cHinweisText}


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
