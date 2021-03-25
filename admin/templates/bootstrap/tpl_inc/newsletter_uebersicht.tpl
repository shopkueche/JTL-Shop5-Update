{include file='tpl_inc/seite_header.tpl' cTitel=__('newsletteroverview') cBeschreibung=__('newsletterdesc') cDokuURL=__('newsletterURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl' action='newsletter.php'}
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'inaktiveabonnenten'} active{/if}" data-toggle="tab" role="tab" href="#inaktiveabonnenten">
                        {__('newsletterSubscripterNotActive')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'alleabonnenten'} active{/if}" data-toggle="tab" role="tab" href="#alleabonnenten">
                        {__('newsletterAllSubscriber')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'neuerabonnenten'} active{/if}" data-toggle="tab" role="tab" href="#neuerabonnenten">
                        {__('newsletterNewSubscriber')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'newsletterqueue'} active{/if}" data-toggle="tab" role="tab" href="#newsletterqueue">
                        {__('newsletterqueue')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'newslettervorlagen'} active{/if}" data-toggle="tab" role="tab" href="#newslettervorlagen">
                        {__('newsletterdraft')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'newslettervorlagenstd'} active{/if}" data-toggle="tab" role="tab" href="#newslettervorlagenstd">
                        {__('newsletterdraftStd')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'newsletterhistory'} active{/if}" data-toggle="tab" role="tab" href="#newsletterhistory">
                        {__('newsletterhistory')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="inaktiveabonnenten" class="tab-pane fade{if $cTab === '' || $cTab === 'inaktiveabonnenten'} active show{/if}">
                {if isset($oNewsletterEmpfaenger_arr) && $oNewsletterEmpfaenger_arr|@count > 0}
                    <div class="search-toolbar mb-3">
                        <form name="suche" method="post" action="newsletter.php">
                            {$jtl_token}
                            <input type="hidden" name="inaktiveabonnenten" value="1" />
                            <input type="hidden" name="tab" value="inaktiveabonnenten" />
                            {if isset($cSucheInaktiv) && $cSucheInaktiv|strlen > 0}
                                <input type="hidden" name="cSucheInaktiv" value="{$cSucheInaktiv}" />
                            {/if}
                            <div class="form-row">
                                <label class="col-sm-auto col-form-label" for="cSucheInaktiv">{__('newslettersubscriberSearch')}:</label>
                                <div class="col-sm-auto mb-3">
                                    <input class="form-control" id="cSucheInaktiv" name="cSucheInaktiv" type="text" value="{if isset($cSucheInaktiv) && $cSucheInaktiv|strlen > 0}{$cSucheInaktiv}{/if}" />
                                </div>
                                <span class="col-sm-auto">
                                    <button name="submitInaktiveAbonnentenSuche" type="submit" class="btn btn-primary btn-block" value="{__('newsletterSearchBTN')}">
                                        <i class="fal fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </form>
                    </div>
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiInaktiveAbos cAnchor='inaktiveabonnenten'}
                    <div id="newsletter-inactive-content">
                        <form name="inaktiveabonnentenForm" method="post" action="newsletter.php">
                            {$jtl_token}
                            <input type="hidden" name="inaktiveabonnenten" value="1" />
                            <input type="hidden" name="tab" value="inaktiveabonnenten" />
                            {if isset($cSucheInaktiv) && $cSucheInaktiv|strlen > 0}
                                <input type="hidden" name="cSucheInaktiv" value="{$cSucheInaktiv}" />
                            {/if}
                            <div>
                                <div class="subheading1">{__('newsletterSubscripterNotActive')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">&nbsp;</th>
                                                <th class="text-left">{__('firstName')}</th>
                                                <th class="text-left">{__('lastName')}</th>
                                                <th class="text-left">{__('customerGroup')}</th>
                                                <th class="text-left">{__('email')}</th>
                                                <th class="text-center">{__('newslettersubscriberdate')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger}
                                            <tr>
                                                <td class="text-left">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" name="kNewsletterEmpfaenger[]" type="checkbox" id="newsletter-recipient-id-{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}" value="{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}">
                                                        <label class="custom-control-label" for="newsletter-recipient-id-{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}"></label>
                                                    </div>
                                                </td>
                                                <td class="text-left">{if $oNewsletterEmpfaenger->cVorname != ""}{$oNewsletterEmpfaenger->cVorname}{else}{$oNewsletterEmpfaenger->newsVorname}{/if}</td>
                                                <td class="text-left">{if $oNewsletterEmpfaenger->cNachname != ""}{$oNewsletterEmpfaenger->cNachname}{else}{$oNewsletterEmpfaenger->newsNachname}{/if}</td>
                                                <td class="text-left">{if isset($oNewsletterEmpfaenger->cName) && $oNewsletterEmpfaenger->cName|strlen > 0}{$oNewsletterEmpfaenger->cName}{else}{__('NotAvailable')}{/if}</td>
                                                <td class="text-left">{$oNewsletterEmpfaenger->cEmail}{if $oNewsletterEmpfaenger->nAktiv == 0} *{/if}</td>
                                                <td class="text-center">{$oNewsletterEmpfaenger->Datum}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <div class="row">
                                        <div class="col-sm-6 col-xl-auto text-left">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);">
                                                <label class="custom-control-label" for="ALLMSGS2">{__('globalSelectAll')}</label>
                                            </div>
                                        </div>
                                        <div class="ml-auto col-sm-6 col-xl-auto">
                                            <button class="btn btn-danger btn-block" name="abonnentloeschenSubmit" type="submit" value="{__('delete')}">
                                                <i class="fas fa-trash-alt"></i> {__('marked')} {__('delete')}
                                            </button>
                                        </div>
                                        <div class="col-sm-6 col-xl-auto">
                                            <button name="abonnentfreischaltenSubmit" type="submit" value="{__('newsletterUnlock')}" class="btn btn-primary btn-block">
                                                <i class="fa fa-thumbs-up"></i> {__('newsletterUnlock')}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="alleabonnenten" class="tab-pane fade{if $cTab === 'alleabonnenten'} active show{/if}">
                {if isset($oAbonnenten_arr) && $oAbonnenten_arr|@count > 0}
                    <div class="search-toolbar mb-3">
                        <form name="suche" method="post" action="newsletter.php">
                            {$jtl_token}
                            <input type="hidden" name="Suche" value="1" />
                            <input type="hidden" name="tab" value="alleabonnenten" />
                            {if isset($cSucheAktiv) && $cSucheAktiv|strlen > 0}
                                <input type="hidden" name="cSucheAktiv" value="{$cSucheAktiv}" />
                            {/if}
                            <div id="newsletter-all-search">
                                <div class="form-row">
                                    <label class="col-sm-auto col-form-label" for="cSucheAktiv">{__('newslettersubscriberSearch')}:</label>
                                    <div class="col-sm-auto mb-3">
                                        <input id="cSucheAktiv" name="cSucheAktiv" class="form-control" type="text" value="{if isset($cSucheAktiv) && $cSucheAktiv|strlen > 0}{$cSucheAktiv}{/if}" />
                                    </div>
                                    <span class="col-sm-auto">
                                        <button name="submitSuche" type="submit" value="{__('newsletterSearchBTN')}" class="btn btn-primary btn-block">
                                            <i class="fal fa-search"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiAlleAbos cAnchor='alleabonnenten'}
                    <!-- Uebersicht Newsletterhistory -->
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newsletterabonnent_loeschen" type="hidden" value="1">
                        <input type="hidden" name="tab" value="alleabonnenten">
                        <div id="newsletter-all-content">
                            <div>
                                <div class="subheading1">{__('newsletterAllSubscriber')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">&nbsp;</th>
                                                <th class="text-left">{__('newslettersubscribername')}</th>
                                                <th class="text-left">{__('customerGroup')}</th>
                                                <th class="text-left">{__('email')}</th>
                                                <th class="text-center">{__('newslettersubscriberdate')}</th>
                                                <th class="text-center">{__('newslettersubscriberLastNewsletter')}</th>
                                                <th class="text-left">{__('newsletterOptInIp')}</th>
                                                <th class="text-center">{__('newsletterOptInDate')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oAbonnenten_arr as $oAbonnenten}
                                            <tr>
                                                <td class="text-left">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" name="kNewsletterEmpfaenger[]" type="checkbox" id="newsletter-abo-id-{$oAbonnenten->kNewsletterEmpfaenger}" value="{$oAbonnenten->kNewsletterEmpfaenger}" />
                                                        <label class="custom-control-label" for="newsletter-abo-id-{$oAbonnenten->kNewsletterEmpfaenger}"></label>
                                                    </div>
                                                </td>
                                                <td class="text-left">{$oAbonnenten->cVorname} {$oAbonnenten->cNachname}</td>
                                                <td class="text-left">{$oAbonnenten->cName}</td>
                                                <td class="text-left">{$oAbonnenten->cEmail}</td>
                                                <td class="text-center">{$oAbonnenten->dEingetragen_de}</td>
                                                <td class="text-center">{$oAbonnenten->dLetzterNewsletter_de}</td>
                                                <td class="text-left">{$oAbonnenten->cOptIp}</td>
                                                <td class="text-center">{$oAbonnenten->optInDate}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <div class="row">
                                        <div class="col-sm-6 col-xl-auto text-left">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);">
                                                <label class="custom-control-label" for="ALLMSGS3">{__('globalSelectAll')}</label>
                                            </div>
                                        </div>
                                        <div class="ml-auto col-sm-6 col-xl-auto">
                                            <button name="loeschen" type="submit" class="btn btn-danger btn-block">
                                                <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {if isset($cSucheAktiv) && $cSucheAktiv|strlen > 0}
                        <form method="post" action="newsletter.php">
                            {$jtl_token}
                            <input name="tab" type="hidden" value="alleabonnenten" />
                            <div class="row">
                                <div class="col-sm-6 col-xl-auto">
                                    <input name="submitAbo" type="submit" value="{__('newsletterNewSearch')}" class="btn btn-primary btn-block" />
                                </div>
                            </div>
                        </form>
                    {/if}
                {/if}
            </div>
            <div id="neuerabonnenten" class="tab-pane fade{if $cTab === 'neuerabonnenten'} active show{/if}">
                <form method="post" action="newsletter.php">
                    {$jtl_token}
                    <input type="hidden" name="newsletterabonnent_neu" value="1">
                    <input name="tab" type="hidden" value="neuerabonnenten">
                    <div class="settings">
                        <div class="subheading1">{__('newsletterNewSubscriber')}</div>
                        <hr class="mb-3">
                        <div>
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cVorname">{__('firstName')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cVorname" id="cVorname" value="{if isset($oNewsletter->cVorname)}{$oNewsletter->cVorname}{/if}" />
                                </div>
                            </div>

                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cNachname">{__('lastName')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cNachname" id="cNachname" value="{if isset($oNewsletter->cNachname)}{$oNewsletter->cNachname}{/if}" />
                                </div>
                            </div>

                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cEmail">{__('email')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="cEmail" id="cEmail" value="{if isset($oNewsletter->cEmail)}{$oNewsletter->cEmail}{/if}" />
                                </div>
                            </div>

                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="kSprache">{__('language')}:</label>
                                <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <select class="custom-select" name="kSprache" id="kSprache">
                                        {foreach $availableLanguages as $language}
                                            <option value="{$language->getId()}">{$language->getLocalizedName()}</option>
                                        {/foreach}
                                    </select>
                                </span>
                            </div>
                        </div>
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div id="newsletterqueue" class="tab-pane fade{if $cTab === 'newsletterqueue'} active show{/if}">
                {if isset($oNewsletterQueue_arr) && $oNewsletterQueue_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiWarteschlange cAnchor='newsletterqueue'}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newsletterqueue" type="hidden" value="1">
                        <input name="tab" type="hidden" value="newsletterqueue">
                        <div id="newsletter-queue-content">
                            <div>
                                <div class="subheading1">{__('newsletterqueue')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1" style="width: 4%;">&nbsp;</th>
                                                <th class="th-2" style="width: 40%;">{__('subject')}</th>
                                                <th class="th-3" style="width: 30%;">{__('newsletterqueuedate')}</th>
                                                <th class="th-4" style="width: 26%;">{__('newsletterqueueimprovement')}</th>
                                                <th class="th-5" style="width: 26%;">{__('newsletterqueuecount')}</th>
                                                <th class="th-6" style="width: 26%;">{__('newsletterqueuecustomergrp')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewsletterQueue_arr as $oNewsletterQueue}
                                            {if isset($oNewsletterQueue->nAnzahlEmpfaenger) && $oNewsletterQueue->nAnzahlEmpfaenger > 0}
                                                <tr>
                                                    <td>
                                                        <div class="custom-control custom-checkbox">
                                                            <input class="custom-control-input" name="kNewsletterQueue[]" type="checkbox" id="newsletter-queue-id-{$oNewsletterQueue->kNewsletterQueue}" value="{$oNewsletterQueue->kNewsletterQueue}">
                                                            <label class="custom-control-label" for="newsletter-queue-id-{$oNewsletterQueue->kNewsletterQueue}"></label>
                                                        </div>
                                                    </td>
                                                    <td>{$oNewsletterQueue->cBetreff}</td>
                                                    <td>{$oNewsletterQueue->Datum}</td>
                                                    <td>{$oNewsletterQueue->nLimitN}</td>
                                                    <td>{$oNewsletterQueue->nAnzahlEmpfaenger}</td>
                                                    <td>
                                                        {foreach $oNewsletterQueue->cKundengruppe_arr as $cKundengruppe}
                                                            {if $cKundengruppe == '0'}{__('newsletterNoAccount')}{if !$cKundengruppe@last}, {/if}{/if}
                                                            {foreach $customerGroups as $customerGroup}
                                                                {if $cKundengruppe == $customerGroup->getID()}{$customerGroup->getName()}{if !$customerGroup@last}, {/if}{/if}
                                                            {/foreach}
                                                        {/foreach}
                                                    </td>
                                                </tr>
                                            {/if}
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <div class="row">
                                        <div class="col-sm-6 col-xl-auto text-left">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessages(this.form);">
                                                <label class="custom-control-label" for="ALLMSGS4">{__('globalSelectAll')}</label>
                                            </div>
                                        </div>
                                        <div class="ml-auto col-sm-6 col-xl-auto">
                                            <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger btn-block">
                                                <i class="fas fa-trash-alt"></i> {__('delete')}
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="newslettervorlagen" class="tab-pane fade{if $cTab === 'newslettervorlagen'} active show{/if}">
                {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiVorlagen cAnchor='newslettervorlagen'}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newslettervorlagen" type="hidden" value="1">
                        <input name="tab" type="hidden" value="newslettervorlagen">
                        <div id="newsletter-vorlagen-content">
                            <div>
                                <div class="subheading1">{__('marked')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">&nbsp;</th>
                                                <th class="th-2">{__('newsletterdraftname')}</th>
                                                <th class="th-3">{__('subject')}</th>
                                                <th class="th-4 text-center">{__('newsletterdraftStdShort')}</th>
                                                <th class="th-5 text-center" style="width: 385px;">{__('options')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewsletterVorlage_arr as $oNewsletterVorlage}
                                            <tr>
                                                <td>
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" name="kNewsletterVorlage[]" type="checkbox" id="newsletter-template-id-{$oNewsletterVorlage->kNewsletterVorlage}" value="{$oNewsletterVorlage->kNewsletterVorlage}">
                                                        <label class="custom-control-label" for="newsletter-template-id-{$oNewsletterVorlage->kNewsletterVorlage}"></label>
                                                    </div>
                                                </td>
                                                <td>{$oNewsletterVorlage->cName}</td>
                                                <td>{$oNewsletterVorlage->cBetreff}</td>
                                                <td class="text-center">
                                                    {if $oNewsletterVorlage->kNewslettervorlageStd > 0}
                                                        {__('yes')}
                                                    {else}
                                                        {__('no')}
                                                    {/if}
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a class="btn btn-link px-2"
                                                           href="newsletter.php?&vorschau={$oNewsletterVorlage->kNewsletterVorlage}&iframe=1&tab=newslettervorlagen&token={$smarty.session.jtl_token}"
                                                           title="{__('preview')}"
                                                           data-toggle="tooltip">
                                                            <span class="icon-hover">
                                                                <span class="fal fa-eye"></span>
                                                                <span class="fas fa-eye"></span>
                                                            </span>
                                                        </a>
                                                        {if $oNewsletterVorlage->kNewslettervorlageStd > 0}
                                                            <a class="btn btn-link px-2"
                                                               href="newsletter.php?newslettervorlagenstd=1&editieren={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&token={$smarty.session.jtl_token}"
                                                               title="{__('modify')}"
                                                               data-toggle="tooltip">
                                                                <span class="icon-hover">
                                                                    <span class="fal fa-edit"></span>
                                                                    <span class="fas fa-edit"></span>
                                                                </span>
                                                            </a>
                                                        {else}
                                                            <a class="btn btn-link px-2"
                                                               href="newsletter.php?newslettervorlagen=1&editieren={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&token={$smarty.session.jtl_token}"
                                                               title="{__('modify')}"
                                                               data-toggle="tooltip">
                                                                <span class="icon-hover">
                                                                    <span class="fal fa-edit"></span>
                                                                    <span class="fas fa-edit"></span>
                                                                </span>
                                                            </a>
                                                        {/if}
                                                        <a class="btn btn-link px-2"
                                                           href="newsletter.php?newslettervorlagen=1&vorbereiten={$oNewsletterVorlage->kNewsletterVorlage}&tab=newslettervorlagen&token={$smarty.session.jtl_token}"
                                                           title="{__('newsletterprepare')}"
                                                           data-toggle="tooltip">
                                                            <span class="icon-hover">
                                                                <span class="fal fa-newspaper"></span>
                                                                <span class="fas fa-newspaper"></span>
                                                            </span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <div class="row">
                                        <div class="col-sm-6 col-xl-auto text-left">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS5" type="checkbox" onclick="AllMessages(this.form);">
                                                <label class="custom-control-label" for="ALLMSGS5">{__('globalSelectAll')}</label>
                                            </div>
                                        </div>
                                        {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                                            <div class="ml-auto col-sm-6 col-xl-auto">
                                                <button class="btn btn-danger btn-block" name="loeschen" type="submit" value="{__('delete')}">
                                                    <i class="fas fa-trash-alt"></i> {__('delete')}
                                                </button>
                                            </div>
                                        {/if}
                                        <div class="{if !(isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0)}ml-auto{/if} col-sm-6 col-xl-auto">
                                            <button name="vorlage_erstellen" class="btn btn-primary btn-block" type="submit">
                                                {__('newsletterdraftcreate')}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newslettervorlagen" type="hidden" value="1">
                        <input name="tab" type="hidden" value="newslettervorlagen">
                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                            <div class="submit {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}btn-group{/if}">
                                <button name="vorlage_erstellen" class="btn btn-primary" type="submit">{__('newsletterdraftcreate')}</button>
                                {if isset($oNewsletterVorlage_arr) && $oNewsletterVorlage_arr|@count > 0}
                                    <button class="btn btn-danger" name="loeschen" type="submit" value="{__('delete')}"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                {/if}
                            </div>
                    </form>
                {/if}
            </div>
            <div id="newslettervorlagenstd" class="tab-pane fade{if $cTab === 'newslettervorlagenstd'} active show{/if}">
                {if isset($oNewslettervorlageStd_arr) && $oNewslettervorlageStd_arr|@count > 0}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newslettervorlagenstd" type="hidden" value="1" />
                        <input name="vorlage_std_erstellen" type="hidden" value="1" />
                        <input name="tab" type="hidden" value="newslettervorlagenstd" />

                        <div id="newsletter-vorlage-std-content">
                            <div>
                                <div class="subheading1">{__('newsletterdraftStd')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">{__('newsletterdraftname')}</th>
                                                <th class="th-2">{__('preview')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewslettervorlageStd_arr as $oNewslettervorlageStd}
                                            <tr>
                                                <td>
                                                    <input name="kNewsletterVorlageStd" id="knvls-{$oNewslettervorlageStd@iteration}" type="radio" value="{$oNewslettervorlageStd->kNewslettervorlageStd}" /> <label for="knvls-{$oNewslettervorlageStd@iteration}">{$oNewslettervorlageStd->cName}</label>
                                                </td>
                                                <td valign="top">{$oNewslettervorlageStd->cBild}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <div class="row">
                                        <div class="ml-auto col-sm-6 col-xl-auto">
                                            <button name="submitVorlageStd" type="submit" value="{__('newsletterdraftStdUse')}" class="btn btn-primary btn-block">
                                                <i class="fa fa-share"></i> {__('newsletterdraftStdUse')}
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="newsletterhistory" class="tab-pane fade{if $cTab === 'newsletterhistory'} active show{/if}">
                {if isset($oNewsletterHistory_arr) && $oNewsletterHistory_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiHistory cAnchor='newsletterhistory'}
                    <form method="post" action="newsletter.php">
                        {$jtl_token}
                        <input name="newsletterhistory" type="hidden" value="1">
                        <input name="tab" type="hidden" value="newsletterhistory">
                        <div id="newsletter-history-content">
                            <div>
                                <div class="subheading1">{__('newsletterhistory')}</div>
                                <hr class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="th-1">&nbsp;</th>
                                                <th class="text-left">{__('newsletterhistorysubject')}</th>
                                                <th class="text-left">{__('newsletterhistorycount')}</th>
                                                <th class="text-left">{__('newsletterqueuecustomergrp')}</th>
                                                <th class="text-center">{__('newsletterhistorydate')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oNewsletterHistory_arr as $oNewsletterHistory}
                                            <tr>
                                                <td class="text-left">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" name="kNewsletterHistory[]" type="checkbox" id="newsletter-history-id-{$oNewsletterHistory->kNewsletterHistory}" value="{$oNewsletterHistory->kNewsletterHistory}">
                                                        <label class="custom-control-label" for="newsletter-history-id-{$oNewsletterHistory->kNewsletterHistory}"></label>
                                                    </div>
                                                </td>
                                                <td class="text-left">
                                                    <a href="newsletter.php?newsletterhistory=1&anzeigen={$oNewsletterHistory->kNewsletterHistory}&tab=newsletterhistory&token={$smarty.session.jtl_token}">{$oNewsletterHistory->cBetreff}</a>
                                                </td>
                                                <td class="text-left">{$oNewsletterHistory->nAnzahl}</td>
                                                <td class="text-left">{$oNewsletterHistory->cKundengruppe}</td>
                                                <td class="text-center">{$oNewsletterHistory->Datum}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <div class="row">
                                        <div class="col-sm-6 col-xl-auto text-left">
                                            <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                                        </div>
                                        </div>
                                        <div class="ml-auto col-sm-6 col-xl-auto">
                                            <button name="loeschen" type="submit" class="btn btn-danger btn-block" value="{__('delete')}">
                                                <i class="fas fa-trash-alt"></i> {__('delete')}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="einstellungen" class="tab-pane fade{if $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='newsletter.php' buttonCaption=__('saveWithIcon') title=__('settings') tab='einstellungen'}
            </div>
        </div>
    </div><!-- .tab-content-->
</div><!-- #content -->
