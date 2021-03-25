{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

We are happy to inform you that the following coupon ({$Kupon->AngezeigterName}) is waiting for you in our online shop:

{if $Kupon->cKuponTyp == $couponTypes.standard}Value of coupon: {$Kupon->cLocalizedWert} {if $Kupon->cWertTyp === 'prozent'}discount{/if}{/if}{if $Kupon->cKuponTyp == $couponTypes.shipping}>You will get free shipping with this coupon!
    This coupon is valid for the following destination countries: {$Kupon->cLieferlaender|upper}{/if}

Coupon code: {$Kupon->cCode}

Valid from {$Kupon->cGueltigAbLong}{if $Kupon->dGueltigBis != 0} until {$Kupon->cGueltigBisLong}{/if}

{if $Kupon->fMindestbestellwert>0}Minimum order value: {$Kupon->cLocalizedMBW}

{else}There is no minimum order value!

{/if}{if $Kupon->nVerwendungenProKunde>1}You may use this coupon in our shop {$Kupon->nVerwendungenProKunde} times.

{elseif $Kupon->nVerwendungenProKunde==0}You may use this coupon in our shop for any number of purchases.

{/if}{if $Kupon->nVerwendungen>0}Please note that this coupon is only valid for a certain amount of uses.

{/if}{if count($Kupon->Kategorien)>0}This coupon can be used for items in the following categories:


    {foreach $Kupon->Kategorien as $Kategorie}
        {$Kategorie->cName} >
        {$Kategorie->cURL}
    {/foreach}{/if}

{if count($Kupon->Artikel)>0}This coupon can be used for the following items:


    {foreach $Kupon->Artikel as $Artikel}
        {$Artikel->cName} >
        {$Artikel->cURLFull}
    {/foreach}{/if}

{if is_array($Kupon->Hersteller) && count($Kupon->Hersteller)>0 && !empty($Kupon->Hersteller[0]->getName())}
    This coupon can be used for the following manufacturers:

    {foreach $Kupon->Hersteller as $Hersteller}
        {$Hersteller->getName()} >
        {$Hersteller->cURL}
    {/foreach}{/if}

Please enter the coupon code during the checkout process.

Enjoy your next purchase in our shop.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}
