<form name="zusatzverpackung" method="post" action="zusatzverpackung.php">
    {$jtl_token}
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="kVerpackung" value="{if isset($kVerpackung)}{$kVerpackung}{/if}" />
    <div class="card">
        <div class="card-header">
            <div class="subheading1">
                {if isset($kVerpackung) && $kVerpackung > 0}{__('zusatzverpackungEdit')}{else}{__('zusatzverpackungCreateTitle')}{/if}
            </div>
            <hr class="mb-n3">
        </div>
        <div class="card-body">
            {foreach $availableLanguages as $key => $language}
            {assign var=cISO value=$language->getIso()}
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-3 col-form-label text-sm-right" for="cName_{$cISO}">{__('name')} ({$language->getLocalizedName()}):</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" id="cName_{$cISO}" name="cName_{$cISO}" type="text" value="{if isset($oVerpackungEdit->oSprach_arr[$cISO]->cName)}{$oVerpackungEdit->oSprach_arr[$cISO]->cName}{/if}" {if $key === 0}required{/if}>
                    </div>
                </div>
            {/foreach}
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-3 col-form-label text-sm-right" for="fBrutto">{__('price')} ({__('gross')}):</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <input class="form-control" name="fBrutto" id="fBrutto" type="text" value="{if isset($oVerpackungEdit->fBrutto)}{$oVerpackungEdit->fBrutto}{/if}" onKeyUp="setzePreisAjax(false, 'WertAjax', this)" required/>
                    <span id="WertAjax">{if isset($oVerpackungEdit->fBrutto)}{getCurrencyConversionSmarty fPreisBrutto=$oVerpackungEdit->fBrutto}{/if}</span>
                </div>
            </div>
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-3 col-form-label text-sm-right" for="fMindestbestellwert">{__('minOrderValue')} ({__('gross')}):</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <input class="form-control" name="fMindestbestellwert" id="fMindestbestellwert" type="text" value="{if isset($oVerpackungEdit->fMindestbestellwert)}{$oVerpackungEdit->fMindestbestellwert}{/if}" onKeyUp="setzePreisAjax(false, 'MindestWertAjax', this)" required/>
                    <span id="MindestWertAjax">{if isset($oVerpackungEdit->fMindestbestellwert)}{getCurrencyConversionSmarty fPreisBrutto=$oVerpackungEdit->fMindestbestellwert}{/if}</span>
                </div>
            </div>
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-3 col-form-label text-sm-right" for="fKostenfrei">{__('zusatzverpackungExemptFromCharge')} ({__('gross')}):</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <input class="form-control" name="fKostenfrei" id="fKostenfrei" type="text" value="{if isset($oVerpackungEdit->fKostenfrei)}{$oVerpackungEdit->fKostenfrei}{/if}" onKeyUp="setzePreisAjax(false, 'KostenfreiAjax', this)" required/>
                    <span id="KostenfreiAjax">{if isset($oVerpackungEdit->fKostenfrei)}{getCurrencyConversionSmarty fPreisBrutto=$oVerpackungEdit->fKostenfrei}{/if}</span>
                </div>
            </div>
            {foreach $availableLanguages as $language}
            {assign var=cISO value=$language->getIso()}
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-3 col-form-label text-sm-right" for="cBeschreibung_{$cISO}">{__('description')} ({$language->getLocalizedName()}):</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <textarea id="cBeschreibung_{$cISO}" name="cBeschreibung_{$cISO}" rows="5" cols="35" class="form-control combo">{if isset($oVerpackungEdit->oSprach_arr[$cISO]->cBeschreibung)}{$oVerpackungEdit->oSprach_arr[$cISO]->cBeschreibung}{/if}</textarea>
                    </div>
                </div>
            {/foreach}
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-3 col-form-label text-sm-right" for="kSteuerklasse">{__('zusatzverpackungTaxClass')}:</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <select id="kSteuerklasse" name="kSteuerklasse" class="custom-select combo">
                        <option value="0">{__('zusatzverpackungAutoTax')}</option>
                        {foreach $taxClasses as $taxClass}
                            <option value="{$taxClass->kSteuerklasse}" {if isset($oVerpackungEdit) && (int)$taxClass->kSteuerklasse === (int)$oVerpackungEdit->kSteuerklasse} selected{/if}>{$taxClass->cName}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-3 col-form-label text-sm-right" for="kKundengruppe">{__('customerGroup')}:</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <select id="kKundengruppe"
                            name="kKundengruppe[]"
                            multiple="multiple"
                            class="selectpicker custom-select combo"
                            required
                            data-selected-text-format="count > 2"
                            data-size="7">
                        <option value="-1"{if isset($oVerpackungEdit) && $oVerpackungEdit->cKundengruppe == '-1'} selected{/if}>{__('all')}</option>
                        <option data-divider="true"></option>
                        {foreach $customerGroups as $customerGroup}
                            {if (isset($oVerpackungEdit->cKundengruppe) && $oVerpackungEdit->cKundengruppe == '-1') || !isset($oVerpackungEdit) || !$oVerpackungEdit}
                                <option value="{$customerGroup->getID()}">{$customerGroup->getName()}</option>
                            {else}
                                <option value="{$customerGroup->getID()}"
                                    {foreach $oVerpackungEdit->kKundengruppe_arr as $kKundengruppe}
                                    {if $customerGroup->getID() === (int)$kKundengruppe} selected{/if}
                                    {/foreach}>
                                    {$customerGroup->getName()}
                                </option>

                            {/if}
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-3 col-form-label text-sm-right" for="nAktiv">{__('active')}:</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <select id="nAktiv" name="nAktiv" class="custom-select combo">
                        <option value="1"{if isset($oVerpackungEdit) && (int)$oVerpackungEdit->nAktiv === 1} selected{/if}>{__('yes')}</option>
                        <option value="0"{if isset($oVerpackungEdit) && (int)$oVerpackungEdit->nAktiv === 0} selected{/if}>{__('no')}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <a class="btn btn-outline-primary btn-block" href="zusatzverpackung.php">
                        {__('cancelWithIcon')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <button class="btn btn-primary btn-block" name="speichern" type="submit">
                        {__('saveWithIcon')}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
