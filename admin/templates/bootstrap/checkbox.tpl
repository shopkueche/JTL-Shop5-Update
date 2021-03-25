{include file='tpl_inc/header.tpl'}

<script type='text/javascript'>
    {literal}
    function aenderAnzeigeLinks(bShow) {
        if (bShow) {
            document.getElementById('InterneLinks').style.display = 'block';
            document.getElementById('InterneLinks').disabled = false;
        } else {
            document.getElementById('InterneLinks').style.display = 'none';
            document.getElementById('InterneLinks').disabled = true;
        }
    }

    function checkFunctionDependency() {
        var elemOrt = document.getElementById('cAnzeigeOrt'),
            elemSF = document.getElementById('kCheckBoxFunktion');

        if (elemSF.options[elemSF.selectedIndex].value == 1) {
            elemOrt.options[2].disabled = true;
        } else if (elemSF.options[elemSF.selectedIndex].value != 1) {
            elemOrt.options[2].disabled = false;
        }
        if (elemOrt.options[elemOrt.selectedIndex].value == 3) {
            elemSF.options[2].disabled = true;
        } else if (elemOrt.options[elemOrt.selectedIndex].value != 3) {
            elemSF.options[2].disabled = false;
        }
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('checkbox') cBeschreibung=__('checkboxDesc') cDokuURL=__('checkboxURL')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'uebersicht'} active{/if}" data-toggle="tab" role="tab" href="#uebersicht">
                        {__('overview')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'erstellen'} active{/if}" data-toggle="tab" role="tab" href="#erstellen">
                        {__('create')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="uebersicht" class="tab-pane fade {if $cTab === '' || $cTab === 'uebersicht'} active show{/if}">
                {if isset($oCheckBox_arr) && $oCheckBox_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination cAnchor='uebersicht'}
                    <div id="tabellenLivesuche">
                        <form name="uebersichtForm" method="post" action="checkbox.php">
                            {$jtl_token}
                            <input type="hidden" name="uebersicht" value="1" />
                            <input type="hidden" name="tab" value="uebersicht" />
                            <div>
                                <div class="subheading1">{__('availableCheckboxes')}</div>
                                <hr class="mb-3">
                                <div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-align-top">
                                            <thead>
                                                <tr>
                                                    <th class="th-1">&nbsp;</th>
                                                    <th class="th-1">{__('name')}</th>
                                                    <th class="th-2">{__('checkboxLink')}</th>
                                                    <th class="th-3">{__('checkboxLocation')}</th>
                                                    <th class="th-4">{__('checkboxFunction')}</th>
                                                    <th class="th-4 text-center">{__('requiredEntry')}</th>
                                                    <th class="th-5 text-center">{__('active')}</th>
                                                    <th class="th-5 text-center">{__('checkboxLogging')}</th>
                                                    <th class="th-6 text-center">{__('sorting')}</th>
                                                    <th class="th-7">{__('customerGroup')}</th>
                                                    <th class="th-8" colspan="2">{__('checkboxDate')}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            {foreach $oCheckBox_arr as $oCheckBoxUebersicht}
                                                <tr>
                                                    <td>
                                                        <div class="custom-control custom-checkbox">
                                                            <input class="custom-control-input" name="kCheckBox[]" id="cb-check-{$oCheckBoxUebersicht@index}" type="checkbox" value="{$oCheckBoxUebersicht->kCheckBox}" />
                                                            <label class="custom-control-label" for="cb-check-{$oCheckBoxUebersicht@index}"></label>
                                                        </div>
                                                    </td>
                                                    <td><label for="cb-check-{$oCheckBoxUebersicht@index}">{$oCheckBoxUebersicht->cName}</label></td>
                                                    <td>{if $oCheckBoxUebersicht->oLink !== null}{$oCheckBoxUebersicht->oLink->getName()}{/if}</td>
                                                    <td>
                                                        {foreach $oCheckBoxUebersicht->kAnzeigeOrt_arr as $kAnzeigeOrt}
                                                            {$cAnzeigeOrt_arr[$kAnzeigeOrt]}{if !$kAnzeigeOrt@last}, {/if}
                                                        {/foreach}
                                                    </td>
                                                    <td>{if isset($oCheckBoxUebersicht->oCheckBoxFunktion->cName)}{$oCheckBoxUebersicht->oCheckBoxFunktion->cName}{/if}</td>

                                                    <td class="text-center">{if $oCheckBoxUebersicht->nPflicht}{__('yes')}{else}{__('no')}{/if}</td>
                                                    <td class="text-center">{if $oCheckBoxUebersicht->nAktiv}<i class="fal fa-check text-success"></i>{else}<i class="fal fa-times text-danger"></i>{/if}</td>
                                                    <td class="text-center">{if $oCheckBoxUebersicht->nLogging}{__('yes')}{else}{__('no')}{/if}</td>
                                                    <td class="text-center">{$oCheckBoxUebersicht->nSort}</td>
                                                    <td>
                                                        {foreach $oCheckBoxUebersicht->kKundengruppe_arr as $id}
                                                            {Kundengruppe::getNameByID($id)}{if !$id@last}, {/if}
                                                        {/foreach}
                                                    </td>
                                                    <td>{$oCheckBoxUebersicht->dErstellt_DE}</td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="checkbox.php?edit={$oCheckBoxUebersicht->kCheckBox}&token={$smarty.session.jtl_token}"
                                                               class="btn btn-link px-2" title="{__('modify')}" data-toggle="tooltip">
                                                                <span class="icon-hover">
                                                                    <span class="fal fa-edit"></span>
                                                                    <span class="fas fa-edit"></span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
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
                                            <button name="checkboxLoeschenSubmit" class="btn btn-danger btn-block" type="submit" value="{__('delete')}">
                                                <i class="fas fa-trash-alt"></i> {__('delete')}
                                            </button>
                                        </div>
                                        <div class="col-sm-6 col-xl-auto">
                                            <button name="checkboxDeaktivierenSubmit" class="btn btn-outline-primary btn-block" type="submit" value="{__('deactivate')}">
                                                <i class="fal fa-times text-danger"></i> {__('deactivate')}
                                            </button>
                                        </div>
                                        <div class="col-sm-6 col-xl-auto">
                                            <button name="checkboxAktivierenSubmit" type="submit" class="btn btn-outline-primary btn-block" value="{__('activate')}">
                                                <i class="fal fa-check text-success"></i> {__('activate')}
                                            </button>
                                        </div>
                                        <div class="col-sm-6 col-xl-auto">
                                            <button name="erstellenShowButton" type="submit" class="btn btn-primary btn-block" value="neue Checkbox erstellen">
                                                <i class="fa fa-share"></i> {__('checkboxCreate')}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    <form method="post" action="checkbox.php">
                        {$jtl_token}
                        <input name="tab" type="hidden" value="erstellen" />
                        <button name="erstellenShowButton" type="submit" class="btn btn-primary" value="neue Checkbox erstellen"><i class="fa fa-share"></i> {__('checkboxCreate')}</button>
                    </form>
                {/if}
            </div>
            <div id="erstellen" class="tab-pane fade {if $cTab === 'erstellen'} active show{/if}">
                <div>
                    <div class="subheading1">{if isset($oCheckBox->kCheckBox) && $oCheckBox->kCheckBox > 0}{__('edit')}{else}{__('create')}{/if}</div>
                        <hr class="mb-3">
                    <div>
                        <form method="post" action="checkbox.php" >
                            {$jtl_token}
                            <input name="erstellen" type="hidden" value="1" />
                            <input name="tab" type="hidden" value="erstellen" />
                            {if isset($oCheckBox->kCheckBox) && $oCheckBox->kCheckBox > 0}
                                <input name="kCheckBox" type="hidden" value="{$oCheckBox->kCheckBox}" />
                            {elseif isset($kCheckBox) && $kCheckBox > 0}
                                <input name="kCheckBox" type="hidden" value="{$kCheckBox}" />
                            {/if}

                            <div class="settings">
                                <div class="form-group form-row align-items-center{if isset($cPlausi_arr.cName)} form-error{/if}">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('name')}</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input id="cName" name="cName" type="text" placeholder="Name" class="form-control" value="{if isset($cPost_arr.cName)}{$cPost_arr.cName}{elseif isset($oCheckBox->cName)}{$oCheckBox->cName}{/if}">
                                    </div>
                                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintCheckboxName')}</div>
                                </div>
                                {if $availableLanguages|@count > 0}
                                    {foreach $availableLanguages as $language}
                                        {assign var=cISO value=$language->getCode()}
                                        {assign var=kSprache value=$language->getId()}
                                        {assign var=cISOText value="cText_$cISO"}
                                        <div class="form-group form-row align-items-center{if isset($cPlausi_arr.cText)} form-error{/if}">
                                            <label class="col col-sm-4 col-form-label text-sm-right" for="cText_{$cISO}">{__('text')} ({$language->getLocalizedName()}):</label>
                                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                <textarea id="cText_{$cISO}" placeholder="Text ({$language->getLocalizedName()})" class="form-control " name="cText_{$cISO}">{if isset($cPost_arr.$cISOText)}{$cPost_arr.$cISOText}{elseif isset($oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText)}{$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}{/if}</textarea>
                                            </div>
                                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintCheckboxText')}</div>
                                        </div>
                                    {/foreach}

                                    {foreach $availableLanguages as $language}
                                        {assign var=cISO value=$language->getCode()}
                                        {assign var=kSprache value=$language->getId()}
                                        {assign var=cISOBeschreibung value="cBeschreibung_$cISO"}
                                        <div class="form-group form-row align-items-center {if isset($cPlausi_arr.cBeschreibung)} form-error{/if}">
                                            <label class="col col-sm-4 col-form-label text-sm-right" for="cBeschreibung_{$cISO}">{__('description')} ({$language->getLocalizedName()}):</label>
                                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                <textarea id="cBeschreibung_{$cISO}" class="form-control" name="cBeschreibung_{$cISO}">{if isset($cPost_arr.$cISOBeschreibung)}{$cPost_arr.$cISOBeschreibung}{elseif isset($oCheckBox->oCheckBoxSprache_arr[$kSprache]->cBeschreibung)}{$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cBeschreibung}{/if}</textarea>
                                            </div>
                                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintCheckboxDescription')}</div>
                                        </div>
                                    {/foreach}
                                {/if}

                                {if isset($oLink_arr) && $oLink_arr|@count > 0}
                                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.kLink)} form-error{/if}">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="nLink">{__('internalLinkTitle')}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <div class="form-row align-items-center">
                                                <div class="col-xs-3 group-radio">
                                                    <label>
                                                    <input id="nLink" name="nLink" type="radio" class="" value="-1" onClick="aenderAnzeigeLinks(false);"{if (!isset($cPlausi_arr.kLink) && (!isset($oCheckBox->kLink) || !$oCheckBox->kLink)) || isset($cPlausi_arr.kLink) && $cPost_arr.nLink == -1} checked="checked"{/if} />
                                                        {__('noLink')}
                                                    </label>
                                                </div>
                                                <div class="col-xs-3 group-radio">
                                                    <label>
                                                        <input id="nLink2" name="nLink" type="radio" class="form-control2" value="1" onClick="aenderAnzeigeLinks(true);"{if (isset($cPost_arr.nLink) && $cPost_arr.nLink == 1) || (isset($oCheckBox->kLink) && $oCheckBox->kLink > 0)} checked="checked"{/if} />
                                                        {__('internalLink')}
                                                    </label>
                                                </div>
                                                <div id="InterneLinks" style="display: none;">
                                                    <select name="kLink" class="custom-select">
                                                        {foreach $oLink_arr as $oLink}
                                                            <option value="{$oLink->kLink}"{if (isset($cPost_arr.kLink) && $cPost_arr.kLink == $oLink->kLink) || (isset($oCheckBox->kLink) && $oCheckBox->kLink == $oLink->kLink)} selected{/if}>{$oLink->cName}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintInternalPage')}</div>
                                    </div>
                                {/if}

                                <div class="form-group form-row align-items-center{if isset($cPlausi_arr.cAnzeigeOrt)} form-error{/if}">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="cAnzeigeOrt">{__('checkboxLocation')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <select id="cAnzeigeOrt"
                                                name="cAnzeigeOrt[]"
                                                class="selectpicker custom-select"
                                                multiple="multiple"
                                                onClick="checkFunctionDependency();"
                                                data-selected-text-format="count > 2"
                                                data-size="7">
                                            {foreach name=anzeigeortarr from=$cAnzeigeOrt_arr key=key item=cAnzeigeOrt}
                                                {assign var=bAOSelect value=false}
                                                {if !isset($cPost_arr.cAnzeigeOrt) && !isset($cPlausi_arr.cAnzeigeOrt) && !isset($oCheckBox->kAnzeigeOrt_arr) && $key == $CHECKBOX_ORT_REGISTRIERUNG}
                                                    {assign var=bAOSelect value=true}
                                                {elseif isset($oCheckBox->kAnzeigeOrt_arr) && $oCheckBox->kAnzeigeOrt_arr|@count > 0}
                                                    {foreach $oCheckBox->kAnzeigeOrt_arr as $kAnzeigeOrt}
                                                        {if $key == $kAnzeigeOrt}
                                                            {assign var=bAOSelect value=true}
                                                        {/if}
                                                    {/foreach}
                                                {elseif isset($cPost_arr.cAnzeigeOrt) && $cPost_arr.cAnzeigeOrt|@count > 0}
                                                    {foreach $cPost_arr.cAnzeigeOrt as $cBoxAnzeigeOrt}
                                                        {if $cBoxAnzeigeOrt == $key}
                                                            {assign var=bAOSelect value=true}
                                                        {/if}
                                                    {/foreach}
                                                {/if}
                                                <option value="{$key}"{if $bAOSelect} selected="selected"{/if}>{$cAnzeigeOrt}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintPlaceToShowCheckbox')}</div>
                                </div>

                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="nPflicht">{__('requiredEntry')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <select id="nPflicht" name="nPflicht" class="custom-select">
                                            <option value="Y"{if (isset($cPost_arr.nPflicht) && $cPost_arr.nPflicht === 'Y') || (isset($oCheckBox->nPflicht) && $oCheckBox->nPflicht == 1)} selected{/if}>
                                                {__('yes')}
                                            </option>
                                            <option value="N"{if (isset($cPost_arr.nPflicht) && $cPost_arr.nPflicht === 'N') || (isset($oCheckBox->nPflicht) && $oCheckBox->nPflicht == 0)} selected{/if}>
                                                {__('no')}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintCheckCheckboxActivation')}</div>
                                </div>

                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('active')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <select id="nAktiv" name="nAktiv" class="custom-select">
                                            <option value="Y"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv === 'Y') || (isset($oCheckBox->nAktiv) && $oCheckBox->nAktiv == 1)} selected{/if}>
                                                {__('yes')}
                                            </option>
                                            <option value="N"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv === 'N') || (isset($oCheckBox->nAktiv) && $oCheckBox->nAktiv == 0)} selected{/if}>
                                                {__('no')}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintCheckboxActive')}</div>
                                </div>

                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="nLogging">{__('checkboxLogging')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <select id="nLogging" name="nLogging" class="custom-select">
                                            <option value="Y"{if (isset($cPost_arr.nLogging) && $cPost_arr.nLogging === 'Y') || (isset($oCheckBox->nLogging) && $oCheckBox->nLogging == 1)} selected{/if}>
                                                {__('yes')}
                                            </option>
                                            <option value="N"{if (isset($cPost_arr.nLogging) && $cPost_arr.nLogging === 'N') || (isset($oCheckBox->nLogging) && $oCheckBox->nLogging == 0)} selected{/if}>
                                                {__('no')}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintCheckboxLogActivate')}</div>
                                </div>

                                <div class="form-group form-row align-items-center{if isset($cPlausi_arr.nSort)} form-error{/if}">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="nSort">{__('sortHigherBottom')}:</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input id="nSort" name="nSort" type="text" class="form-control" value="{if isset($cPost_arr.nSort)}{$cPost_arr.nSort}{elseif isset($oCheckBox->nSort)}{$oCheckBox->nSort}{/if}" />
                                    </div>
                                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintCheckboxOrder')}</div>
                                </div>

                                {if isset($oCheckBoxFunktion_arr) && $oCheckBoxFunktion_arr|@count > 0}
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="kCheckBoxFunktion">{__('specialShopFunction')}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <select class="custom-select" id="kCheckBoxFunktion" name="kCheckBoxFunktion" onclick="checkFunctionDependency();">
                                                <option value="0"></option>
                                                {foreach $oCheckBoxFunktion_arr as $oCheckBoxFunktion}
                                                    <option value="{$oCheckBoxFunktion->kCheckBoxFunktion}"{if (isset($cPost_arr.kCheckBoxFunktion) && $cPost_arr.kCheckBoxFunktion == $oCheckBoxFunktion->kCheckBoxFunktion) || (isset($oCheckBox->kCheckBoxFunktion) && $oCheckBox->kCheckBoxFunktion == $oCheckBoxFunktion->kCheckBoxFunktion)} selected{/if}>{$oCheckBoxFunktion->cName}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintCheckboxFunction')}</div>
                                    </div>
                                {/if}

                                {if $customerGroups|@count > 0}
                                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.kKundengruppe)} form-error{/if}">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppe">{__('customerGroup')}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <select id="kKundengruppe"
                                                    name="kKundengruppe[]"
                                                    class="selectpicker custom-select"
                                                    multiple="multiple"
                                                    data-selected-text-format="count > 2"
                                                    data-size="7"
                                                    data-actions-box="true">
                                            {foreach name=kundengruppen from=$customerGroups key=key item=customerGroup}
                                                {assign var=bKGSelect value=false}
                                                {if !isset($cPost_arr.kKundengruppe) && !isset($cPlausi_arr.kKundengruppe) && !isset($oCheckBox->kKundengruppe_arr) && $customerGroup->isDefault()}
                                                    {assign var=bKGSelect value=true}
                                                {elseif isset($oCheckBox->kKundengruppe_arr) && $oCheckBox->kKundengruppe_arr|@count > 0}
                                                    {foreach $oCheckBox->kKundengruppe_arr as $kKundengruppe}
                                                        {if $kKundengruppe == $customerGroup->getID()}
                                                            {assign var=bKGSelect value=true}
                                                        {/if}
                                                    {/foreach}
                                                {elseif isset($cPost_arr.kKundengruppe) && $cPost_arr.kKundengruppe|@count > 0}
                                                    {foreach $cPost_arr.kKundengruppe as $kKundengruppe}
                                                        {if $kKundengruppe == $customerGroup->getID()}
                                                            {assign var=bKGSelect value=true}
                                                        {/if}
                                                    {/foreach}
                                                {/if}
                                                <option value="{$customerGroup->getID()}"{if $bKGSelect} selected{/if}>{$customerGroup->getName()}</option>
                                            {/foreach}
                                            </select>
                                        </div>
                                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('hintCheckboxCustomerGroup')}</div>
                                    </div>
                                {/if}
                            </div>
                        </div>
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <a class="btn btn-outline-primary btn-block" href="checkbox.php">
                                        {__('cancelWithIcon')}
                                    </a>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{if (isset($cPost_arr.nLink) && $cPost_arr.nLink == 1) || (isset($oCheckBox->kLink) && $oCheckBox->kLink > 0)}
    <script type="text/javascript">
        aenderAnzeigeLinks(true);
    </script>
{/if}
{include file='tpl_inc/footer.tpl'}
