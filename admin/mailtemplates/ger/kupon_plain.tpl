{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

wir freuen uns, Ihnen mitteilen zu dürfen, dass in unserem Onlineshop folgender Coupon ({$Kupon->AngezeigterName}) für Sie bereitliegt:

{if $Kupon->cKuponTyp == $couponTypes.standard}Kuponwert: {$Kupon->cLocalizedWert} {if $Kupon->cWertTyp === 'prozent'}Rabatt auf den gesamten Einkauf{/if}{/if}{if $Kupon->cKuponTyp == $couponTypes.shipping}Mit diesem Coupon können Sie versandkostenfrei bei uns einkaufen!
    Er gilt für folgende Lieferländer: {$Kupon->cLieferlaender|upper}{/if}

Couponcode: {$Kupon->cCode}

Gültig vom {$Kupon->cGueltigAbLong}{if $Kupon->dGueltigBis != 0} bis zum {$Kupon->cGueltigBisLong}{/if}

{if $Kupon->fMindestbestellwert>0}Mindestbestellwert: {$Kupon->cLocalizedMBW}

{else}Es gibt keinen Mindestbestellwert!

{/if}{if $Kupon->nVerwendungenProKunde>1}Sie dürfen diesen Coupon für insgesamt {$Kupon->nVerwendungenProKunde} Einkäufe bei uns nutzen.

{elseif $Kupon->nVerwendungenProKunde==0}Sie dürfen diesen Coupon für beliebig viele Einkäufe bei uns nutzen.

{/if}{if $Kupon->nVerwendungen>0}Bitte beachten Sie, dass dieser Coupon auf eine maximale Verwendungsanzahl begrenzt ist.

{/if}{if count($Kupon->Kategorien)>0}Der Coupon gilt für folgende Kategorien:


    {foreach $Kupon->Kategorien as $Kategorie}
        {$Kategorie->cName} >
        {$Kategorie->cURL}
    {/foreach}{/if}

{if count($Kupon->Artikel)>0}Der Coupon gilt für folgende Artikel:


    {foreach $Kupon->Artikel as $Artikel}
        {$Artikel->cName} >
        {$Artikel->cURLFull}
    {/foreach}{/if}

{if is_array($Kupon->Hersteller) && count($Kupon->Hersteller)>0 && !empty($Kupon->Hersteller[0]->getName())}
    Der Coupon gilt für folgende Hersteller:

    {foreach $Kupon->Hersteller as $Hersteller}
        {$Hersteller->getName()} >
        {$Hersteller->cURL}
    {/foreach}{/if}

Sie lösen den Coupon ein, indem Sie beim Bestellvorgang den Couponcode in das vorgesehene Feld eintragen.

Viel Spaß bei Ihrem nächsten Einkauf in unserem Shop.

Mit freundlichem Gruß
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
