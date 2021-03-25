<p>Dear shop owner,</p>

<p>Customer {if empty($oKunde->cVorname) && empty($oKunde->cNachname)}{$oKunde->cMail}{else}{$oKunde->cVorname} {$oKunde->cNachname}{/if} selected the following checkbox option under {$cAnzeigeOrt}:</p>

<p>
	{assign var=kSprache value=$oSprache->kSprache}
	- {$oCheckBox->cName}, {$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}
</p>
