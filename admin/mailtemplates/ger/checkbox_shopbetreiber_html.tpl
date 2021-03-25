<p>Sehr geehrter Shopbetreiber,</p>

<p>der Kunde {if empty($oKunde->cVorname) && empty($oKunde->cNachname)}{$oKunde->cMail}{else}{$oKunde->cVorname} {$oKunde->cNachname}{/if} hat im Bereich {$cAnzeigeOrt} die folgende Checkboxoption gewählt:</p>

<p>
	{assign var=kSprache value=$oSprache->kSprache}
	- {$oCheckBox->cName}, {$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}
</p>
