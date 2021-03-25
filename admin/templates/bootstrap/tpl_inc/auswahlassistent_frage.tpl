{if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}
    {assign var=subheading value=__('aaQuestionEdit')}
    {assign var=cTitel value=__('auswahlassistent')|cat:' - '|cat:$subheading}
{else}
    {assign var=subheading value=__('aaQuestion')}
    {assign var=cTitel value=__('auswahlassistent')|cat:' - '|cat:$subheading}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('auswahlassistentDesc')
cDokuURL=__('auswahlassistentURL')}

<div id="content">
    {if !isset($noModule) || !$noModule}
        <form class="navbar-form settings" method="post" action="auswahlassistent.php">
            {$jtl_token}
            <input name="speichern" type="hidden" value="1">
            <input name="kSprache" type="hidden" value="{$languageID}">
            <input name="tab" type="hidden" value="frage">
            <input name="a" type="hidden" value="addQuest">
            {if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}
                <input class="form-control" name="kAuswahlAssistentFrage" type="hidden"
                       value="{if isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0}{$kAuswahlAssistentFrage}{else}{$oFrage->kAuswahlAssistentFrage}{/if}">
            {/if}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{$subheading}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.cFrage)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cFrage">
                            {__('question')}:
                        </label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input id="cFrage" class="form-control"
                                   name="cFrage" type="text"
                                   value="{if isset($cPost_arr.cFrage)}{$cPost_arr.cFrage}{elseif isset($oFrage->cFrage)}{$oFrage->cFrage}{/if}">
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc="{__('hintQuestionName')}"}</div>
                    </div>

                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.kAuswahlAssistentGruppe)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kAuswahlAssistentGruppe">
                            {__('group')}:
                        </label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="kAuswahlAssistentGruppe" name="kAuswahlAssistentGruppe" class="custom-select">
                                <option value="-1">{__('aaChoose')}</option>
                                {foreach $oAuswahlAssistentGruppe_arr as $oAuswahlAssistentGruppe}
                                    <option value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                            {if isset($oAuswahlAssistentGruppe->kAuswahlAssistentGruppe) && ((isset($cPost_arr.kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $cPost_arr.kAuswahlAssistentGruppe) || (isset($oFrage->kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $oFrage->kAuswahlAssistentGruppe))} selected{/if}>{$oAuswahlAssistentGruppe->cName}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc="{__('hintQuestionGroup')}"}</div>
                    </div>

                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.kMerkmal)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kMM">{__('attribute')}
                            {if isset($cPlausi_arr.kMerkmal) && $cPlausi_arr.kMerkmal == 2 }<span class="fillout">{__('aaMerkmalTaken')}</span>{/if}:
                        </label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="kMM" name="kMerkmal" class="custom-select">
                                <option value="-1">{__('aaChoose')}</option>
                                {foreach $oMerkmal_arr as $oMerkmal}
                                    <option value="{$oMerkmal->kMerkmal}"{if (isset($cPost_arr.kMerkmal) && $oMerkmal->kMerkmal == $cPost_arr.kMerkmal) || (isset($oFrage->kMerkmal) && $oMerkmal->kMerkmal == $oFrage->kMerkmal)} selected{/if}>{$oMerkmal->cName}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc="{__('hintQuestionAttribute')}"}</div>
                    </div>

                    <div class="form-group form-row align-items-center{if isset($cPlausi_arr.nSort)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nSort">
                            {__('sorting')}:
                        </label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input id="nSort" class="form-control"
                                   name="nSort" type="text"
                                   value="{if isset($cPost_arr.nSort)}{$cPost_arr.nSort}{elseif isset($oFrage->nSort)}{$oFrage->nSort}{else}1{/if}">
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {getHelpDesc cDesc="{__('hintQuestionPosition')}"}
                        </div>
                    </div>

                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('active')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="nAktiv" class="custom-select" name="nAktiv">
                                <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 1)} selected{/if}>
                                    {__('yes')}
                                </option>
                                <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 0)} selected{/if}>
                                    {__('no')}
                                </option>
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {getHelpDesc cDesc="{__('hintQuestionActive')}"}
                        </div>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a href="auswahlassistent.php" class="btn btn-outline-primary btn-block">{__('cancelWithIcon')}</a>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="speichernSubmit" type="submit" class="btn btn-primary btn-block">{__('saveWithIcon')}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-danger">{__('noModuleAvailable')}</div>
    {/if}
</div>

{include file='tpl_inc/footer.tpl'}
