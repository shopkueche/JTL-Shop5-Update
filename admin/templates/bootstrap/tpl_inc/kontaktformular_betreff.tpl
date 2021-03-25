{include file='tpl_inc/seite_header.tpl' cTitel=__('contactformSubject') cBeschreibung=__('contanctformSubjectDesc')}
<div id="content">
    <form name="einstellen" method="post" action="kontaktformular.php">
        {$jtl_token}
        <input type="hidden" name="kKontaktBetreff" value="{if isset($Betreff->kKontaktBetreff)}{$Betreff->kKontaktBetreff}{/if}" />
        <input type="hidden" name="betreff" value="1" />
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('contactformSubject')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="settings">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('subject')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" name="cName" id="cName" value="{if isset($Betreff->cName)}{$Betreff->cName}{/if}" tabindex="1" required />
                        </div>
                    </div>
                    {foreach $availableLanguages as $language}
                        {assign var=cISO value=$language->getIso()}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cName_{$cISO}">{__('showedName')} ({$language->getLocalizedName()}):</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="text" class="form-control" name="cName_{$cISO}" id="cName_{$cISO}" value="{if isset($Betreffname[$cISO])}{$Betreffname[$cISO]}{/if}" tabindex="2" />
                            </div>
                        </div>
                    {/foreach}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cMail">{__('mail')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" name="cMail" id="cMail" value="{if isset($Betreff->cMail)}{$Betreff->cMail}{/if}" tabindex="3" required />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cKundengruppen">{__('restrictedToCustomerGroups')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="selectpicker custom-select"
                                    name="cKundengruppen[]"
                                    multiple="multiple"
                                    id="cKundengruppen"
                                    data-selected-text-format="count > 2"
                                    data-size="7">
                                <option value="0" {if isset($gesetzteKundengruppen[0]) && $gesetzteKundengruppen[0] === true}selected{/if}>{__('allCustomerGroups')}</option>
                                <option data-divider="true"></option>
                                {foreach $kundengruppen as $kundengruppe}
                                    {assign var=kKundengruppe value=$kundengruppe->kKundengruppe}
                                    <option value="{$kundengruppe->kKundengruppe}" {if isset($gesetzteKundengruppen[$kKundengruppe])}selected{/if}>{$kundengruppe->cName}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('multipleChoice')}</div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nSort">{__('sortNo')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" class="form-control" name="nSort" id="nSort" value="{if isset($Betreff->nSort)}{$Betreff->nSort}{/if}" tabindex="4" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="button" onclick="window.location.href='kontaktformular.php?tab=subjects'" class="btn btn-outline-primary btn-block">
                            {__('cancelWithIcon')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
