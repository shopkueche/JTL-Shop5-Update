{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
We are happy to inform you that the following coupon ({$Kupon->AngezeigterName}) is waiting for you in our online shop:<br>
<br>
{if $Kupon->cKuponTyp == $couponTypes.standard}
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="column mobile-left" width="25%" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Value of coupon:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Kupon->cLocalizedWert} {if $Kupon->cWertTyp === 'prozent'}discount{/if}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Coupon code:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Kupon->cCode}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Minimum order value:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{if $Kupon->fMindestbestellwert>0}{$Kupon->cLocalizedMBW}{else}There is no minimum order value!{/if}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table><br>
{/if}
{if $Kupon->cKuponTyp == $couponTypes.shipping}
	You will get free shipping with this coupon!<br>
    This coupon is valid for the following destination countries: {$Kupon->cLieferlaender|upper}<br>
	<br>
{/if}

Valid from {$Kupon->cGueltigAbLong}{if $Kupon->dGueltigBis != 0} until {$Kupon->cGueltigBisLong}{/if}<br>
<br>
{if $Kupon->nVerwendungenProKunde>1}
	You may use this coupon in our shop {$Kupon->nVerwendungenProKunde} times<br>
	<br>
{elseif $Kupon->nVerwendungenProKunde==0}
	You may use this coupon in our shop for any number of purchases.<br>
	<br>
{/if}

{if $Kupon->nVerwendungen>0}
	Please note that this coupon is only valid for a limited time, so be quick.<br>
	<br>
{/if}

{if count($Kupon->Kategorien)>0}
	This coupon can be used for items in the following categories:<br>
    {foreach $Kupon->Kategorien as $Kategorie}
        <a href="{$Kategorie->cURL}">{$Kategorie->cName}</a><br>
    {/foreach}
{/if}
<br>
{if count($Kupon->Artikel)>0}This coupon can be used for the following items:<br>
    {foreach $Kupon->Artikel as $Artikel}
        <a href="{$Artikel->cURLFull}">{$Artikel->cName}</a><br>
    {/foreach}
{/if}<br>

{if is_array($Kupon->Hersteller) && count($Kupon->Hersteller)>0 && !empty($Kupon->Hersteller[0]->getName())}
<br>
	This coupon can be used for the following manufacturers:<br>
	{foreach $Kupon->Hersteller as $Hersteller}
		<a href="{$Hersteller->cURL}">{$Hersteller->getName()}</a><br>
	{/foreach}
	<br>
	<br>
{/if}

Please enter the coupon code during the checkout process.<br>
<br>
Enjoy your next purchase in our shop.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}
