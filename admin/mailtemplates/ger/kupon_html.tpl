{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
wir freuen uns, Ihnen mitteilen zu dürfen, dass in unserem Onlineshop folgender Coupon ({$Kupon->AngezeigterName}) für Sie bereitliegt:<br>
<br>
{if $Kupon->cKuponTyp == $couponTypes.standard}
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="column mobile-left" width="25%" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Couponwert:</strong>
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
							{$Kupon->cLocalizedWert} {if $Kupon->cWertTyp === 'prozent'}Rabatt auf den gesamten Einkauf{/if}
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
							<strong>Couponcode:</strong>
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
							<strong>Mindestbestellwert:</strong>
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
							{if $Kupon->fMindestbestellwert>0}{$Kupon->cLocalizedMBW}{else}Es gibt keinen Mindestbestellwert!{/if}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table><br>
{/if}
{if $Kupon->cKuponTyp == $couponTypes.shipping}
	Mit diesem Coupon können Sie versandkostenfrei bei uns einkaufen!<br>
	Er gilt für folgende Lieferländer: {$Kupon->cLieferlaender|upper}<br>
	<br>
{/if}

Gültig vom {$Kupon->cGueltigAbLong}{if $Kupon->dGueltigBis != 0} bis zum {$Kupon->cGueltigBisLong}{/if}<br>
<br>
{if $Kupon->nVerwendungenProKunde>1}
	Sie dürfen diesen Coupon für insgesamt {$Kupon->nVerwendungenProKunde} Einkäufe bei uns nutzen.<br>
	<br>
{elseif $Kupon->nVerwendungenProKunde==0}
	Sie dürfen diesen Coupon für beliebig viele Einkäufe bei uns nutzen.<br>
	<br>
{/if}

{if $Kupon->nVerwendungen>0}
	Bitte beachten Sie, dass dieser Coupon auf eine maximale Verwendungsanzahl begrenzt ist.<br>
	<br>
{/if}

{if count($Kupon->Kategorien)>0}
	Der Coupon gilt für folgende Kategorien:<br>
    {foreach $Kupon->Kategorien as $Kategorie}
        <a href="{$Kategorie->cURL}">{$Kategorie->cName}</a><br>
    {/foreach}
{/if}
<br>
{if count($Kupon->Artikel)>0}Der Coupon gilt für folgende Artikel:<br>
    {foreach $Kupon->Artikel as $Artikel}
        <a href="{$Artikel->cURLFull}">{$Artikel->cName}</a><br>
    {/foreach}
{/if}<br>

{if is_array($Kupon->Hersteller) && count($Kupon->Hersteller)>0 && !empty($Kupon->Hersteller[0]->getName())}
	<br>
	Der Coupon gilt für folgende Hersteller:<br>
	{foreach $Kupon->Hersteller as $Hersteller}
		<a href="{$Hersteller->cURL}">{$Hersteller->getName()}</a><br>
	{/foreach}
	<br>
	<br>
{/if}
Sie lösen den Coupon ein, indem Sie beim Bestellvorgang den Couponcode in das vorgesehene Feld eintragen.<br>
<br>
Viel Spaß bei Ihrem nächsten Einkauf in unserem Shop.<br>
<br>
Mit freundlichem Gruß<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
