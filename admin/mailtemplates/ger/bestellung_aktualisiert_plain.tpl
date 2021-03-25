{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

Ihre Bestellung bei {$Einstellungen.global.global_shopname} wurde aktualisiert.

Ihre Bestellung mit Bestellnummer {$Bestellung->cBestellNr} umfasst folgende Positionen:

{foreach $Bestellung->Positionen as $Position}

    {if $Position->nPosTyp == 1}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen === 'Y' && $Position->cLieferstatus}

        Lieferzeit: {$Position->cLieferstatus}{/if}
        {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}

            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}{/foreach}
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
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{/if}
{/foreach}

{if $Einstellungen.global.global_steuerpos_anzeigen !== 'N'}{foreach $Bestellung->Steuerpositionen as $Steuerposition}
    {$Steuerposition->cName}: {$Steuerposition->cPreisLocalized}
{/foreach}{/if}
{if isset($Bestellung->GuthabenNutzen) && $Bestellung->GuthabenNutzen == 1}
    Gutschein: -{$Bestellung->GutscheinLocalized}
{/if}

Gesamtsumme: {$Bestellung->WarensummeLocalized[0]}


Ihre Rechnungsadresse:

{$Kunde->cVorname} {$Kunde->cNachname}
{$Kunde->cStrasse} {$Kunde->cHausnummer}
{if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}
{/if}{$Kunde->cPLZ} {$Kunde->cOrt}
{if $Kunde->cBundesland}{$Kunde->cBundesland}
{/if}{$Kunde->cLand}
{if $Kunde->cTel}Tel.: {$Kunde->cTel}
{/if}{if $Kunde->cMobil}Mobil: {$Kunde->cMobil}
{/if}{if $Kunde->cFax}Fax: {$Kunde->cFax}
{/if}
E-Mail: {$Kunde->cMail}
{if $Kunde->cUSTID}Ust-ID: {$Kunde->cUSTID}
{/if}

{if $Bestellung->Lieferadresse->kLieferadresse>0}
    Ihre Lieferadresse:

    {$Bestellung->Lieferadresse->cVorname} {$Bestellung->Lieferadresse->cNachname}
    {$Bestellung->Lieferadresse->cStrasse} {$Bestellung->Lieferadresse->cHausnummer}
    {if $Bestellung->Lieferadresse->cAdressZusatz}{$Bestellung->Lieferadresse->cAdressZusatz}
    {/if}{$Bestellung->Lieferadresse->cPLZ} {$Bestellung->Lieferadresse->cOrt}
    {if $Bestellung->Lieferadresse->cBundesland}{$Bestellung->Lieferadresse->cBundesland}
    {/if}{$Bestellung->Lieferadresse->cLand}
    {if $Bestellung->Lieferadresse->cTel}Tel.: {$Bestellung->Lieferadresse->cTel}
    {/if}{if $Bestellung->Lieferadresse->cMobil}Mobil: {$Bestellung->Lieferadresse->cMobil}
{/if}{if $Bestellung->Lieferadresse->cFax}Fax: {$Bestellung->Lieferadresse->cFax}
{/if}{if $Bestellung->Lieferadresse->cMail}E-Mail: {$Bestellung->Lieferadresse->cMail}
{/if}
{else}
    Lieferadresse ist gleich Rechnungsadresse.
{/if}

Sie haben folgende Zahlungsart gewählt: {$Bestellung->cZahlungsartName}

{if isset($Zahlungsart->cHinweisText) && $Zahlungsart->cHinweisText|strlen > 0}  {$Zahlungsart->cHinweisText}


{/if}

{if $Bestellung->Zahlungsart->cModulId === 'za_rechnung_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_lastschrift_jtl'}
    Wir belasten in Kürze folgendes Bankkonto mit der fälligen Summe:

    Kontoinhaber: {$Bestellung->Zahlungsinfo->cInhaber}
    IBAN: {$Bestellung->Zahlungsinfo->cIBAN}
    BIC: {$Bestellung->Zahlungsinfo->cBIC}
    Bank: {$Bestellung->Zahlungsinfo->cBankName}

{elseif $Bestellung->Zahlungsart->cModulId === 'za_barzahlung_jtl'}
{elseif $Bestellung->Zahlungsart->cModulId === 'za_paypal_jtl'}
    Falls Sie Ihre Zahlung per PayPal noch nicht durchgeführt haben, nutzen Sie folgende E-Mail-Adresse als Empfänger: {$Einstellungen.zahlungsarten.zahlungsart_paypal_empfaengermail}
{/if}

Über den weiteren Verlauf Ihrer Bestellung werden wir Sie jeweils gesondert informieren.


Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
