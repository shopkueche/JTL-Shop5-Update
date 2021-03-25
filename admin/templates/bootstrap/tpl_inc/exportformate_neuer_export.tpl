{if !isset($Exportformat->kExportformat)}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('newExportformat')}
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('modifyExportformat')}
{/if}
<div id="content">
    <form name="wxportformat_erstellen" method="post" action="exportformate.php">
        {$jtl_token}
        <input type="hidden" name="neu_export" value="1" />
        <input type="hidden" name="kExportformat" value="{if isset($Exportformat->kExportformat)}{$Exportformat->kExportformat}{/if}" />
        {if isset($Exportformat->bPluginContentFile) && $Exportformat->bPluginContentFile}
            <input type="hidden" name="bPluginContentFile" value="1" />
        {/if}
        {if !empty($Exportformat->kPlugin)}
            <input type="hidden" name="kPlugin" value="{$Exportformat->kPlugin}" />
        {/if}
        <div class="card settings">
            <div class="card-header">
                <div class="subheading1">
                    {if !isset($Exportformat->kExportformat)}
                        {__('newExportformat')}
                    {else}
                        {__('modifyExportformat')} - {if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($Exportformat->cName)}{$Exportformat->cName}{/if}
                    {/if}
                </div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="form-group form-row align-items-center{if isset($cPlausiValue_arr.cName)} form-error{/if}">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('name')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" name="cName" id="cName" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($Exportformat->cName)}{$Exportformat->cName}{/if}" tabindex="1" />
                    </div>
                </div>
                <div class="form-group form-row align-items-center item">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kSprache">{__('language')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select class="custom-select" name="kSprache" id="kSprache">
                            {foreach $availableLanguages as $language}
                                <option value="{$language->getId()}" {if isset($Exportformat->kSprache) && $Exportformat->kSprache == $language->getId() || (isset($cPlausiValue_arr.kSprache) && $cPlausiValue_arr.kSprache == $language->getId())}selected{/if}>{$language->getLocalizedName()}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center item">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kWaehrung">{__('currency')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select class="custom-select" name="kWaehrung" id="kWaehrung">
                            {foreach $waehrungen as $waehrung}
                                <option value="{$waehrung->kWaehrung}" {if isset($Exportformat->kSprache) && $Exportformat->kWaehrung == $waehrung->kWaehrung || (isset($cPlausiValue_arr.kWaehrung) && $cPlausiValue_arr.cName == $waehrung->kWaehrung)}selected{/if}>{$waehrung->cName}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center item">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kKampagne">{__('campaigns')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select class="custom-select" name="kKampagne" id="kKampagne">
                            <option value="0"></option>
                            {foreach $oKampagne_arr as $oKampagne}
                                <option value="{$oKampagne->kKampagne}" {if isset($Exportformat->kSprache) && $Exportformat->kKampagne == $oKampagne->kKampagne || (isset($cPlausiValue_arr.kKampagne) && $cPlausiValue_arr.kKampagne == $oKampagne->kKampagne)}selected{/if}>{$oKampagne->cName}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center item">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppe">{__('customerGroup')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select class="custom-select" name="kKundengruppe" id="kKundengruppe">
                            {foreach $kundengruppen as $kdgrp}
                                <option value="{$kdgrp->kKundengruppe}" {if isset($Exportformat->kSprache) && $Exportformat->kKundengruppe == $kdgrp->kKundengruppe || (isset($cPlausiValue_arr.kKundengruppe) && $cPlausiValue_arr.kKundengruppe == $kdgrp->kKundengruppe)}selected{/if}>{$kdgrp->cName}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center item">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cKodierung">{__('encoding')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select class="custom-select" name="cKodierung" id="cKodierung">
                            <option value="ASCII" {if (isset($Exportformat->cKodierung) && $Exportformat->cKodierung === 'ASCII') || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung === 'ASCII')}selected{/if}>
                                ASCII
                            </option>
                            <option value="UTF-8" {if (isset($Exportformat->cKodierung) && $Exportformat->cKodierung === 'UTF-8') || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung === 'UTF-8')}selected{/if}>
                                UTF-8 + BOM
                            </option>
                            <option value="UTF-8noBOM" {if (isset($Exportformat->cKodierung) && $Exportformat->cKodierung === 'UTF-8noBOM') || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung === 'UTF-8noBOM')}selected{/if}>
                                UTF-8
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center item">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="nUseCache">{__('useCache')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select class="custom-select" name="nUseCache" id="nUseCache">
                            <option value="1" {if (isset($Exportformat->nUseCache) && $Exportformat->nUseCache === 1)}selected{/if}>{__('yes')}</option>
                            <option value="0" {if (!isset($Exportformat->nUseCache) || $Exportformat->nUseCache === 0)}selected{/if}>{__('no')}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group form-row align-items-center item">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="nVarKombiOption">{__('varikombiOption')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select class="custom-select" name="nVarKombiOption" id="nVarKombiOption">
                            <option value="1" {if (isset($Exportformat->nVarKombiOption) && $Exportformat->nVarKombiOption == 1) || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 1)}selected{/if}>{__('varikombiOption1')}</option>
                            <option value="2" {if (isset($Exportformat->nVarKombiOption) && $Exportformat->nVarKombiOption == 2) || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 2)}selected{/if}>{__('varikombiOption2')}</option>
                            <option value="3" {if (isset($Exportformat->nVarKombiOption) && $Exportformat->nVarKombiOption == 3) || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 3)}selected{/if}>{__('varikombiOption3')}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group form-row align-items-center item">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="nSplitgroesse">{__('splitSize')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" name="nSplitgroesse" id="nSplitgroesse" value="{if isset($cPostVar_arr.nSplitgroesse)}{$cPostVar_arr.nSplitgroesse}{elseif isset($Exportformat->nSplitgroesse)}{$Exportformat->nSplitgroesse}{/if}" tabindex="2" />
                    </div>
                </div>

                <div class="form-group form-row align-items-center item{if isset($cPlausiValue_arr.cDateiname)} form-error{/if}">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cDateiname">{__('filename')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" name="cDateiname" id="cDateiname" value="{if isset($cPostVar_arr.cDateiname)}{$cPostVar_arr.cDateiname}{elseif isset($Exportformat->cDateiname)}{$Exportformat->cDateiname}{/if}" tabindex="2" />
                    </div>
                </div>
                {if !isset($Exportformat->bPluginContentFile)|| !$Exportformat->bPluginContentFile}
                    <p>
                        <label for="cKopfzeile">
                            {__('header')}:
                            {getHelpDesc placement='right' cDesc=__('onlyIfNeeded')}
                        </label>
                        <textarea name="cKopfzeile" id="cKopfzeile" class="codemirror smarty field">{if isset($cPostVar_arr.cKopfzeile)}{$cPostVar_arr.cKopfzeile|replace:"\t":"<tab>"}{elseif isset($Exportformat->cKopfzeile)}{$Exportformat->cKopfzeile}{/if}</textarea>
                    </p>
                    <p>
                        <label for="cContent">
                            {__('template')}:
                            {getHelpDesc placement='right' cDesc=__('smartyRules')}
                        </label>
                        <textarea name="cContent" id="cContent" class="codemirror smarty field{if isset($oSmartyError)}fillout{/if}">{if isset($cPostVar_arr.cContent)}{$cPostVar_arr.cContent|replace:"\t":"<tab>"}{elseif isset($Exportformat->cContent)}{$Exportformat->cContent}{/if}</textarea>
                    </p>
                    <p>
                        <label for="cFusszeile">
                            {__('footer')}:
                            {getHelpDesc placement='right' cDesc=__('onlyIfNeededFooter')}
                        </label>
                        <textarea name="cFusszeile" id="cFusszeile" class="codemirror smarty field">{if isset($cPostVar_arr.cFusszeile)}{$cPostVar_arr.cFusszeile|replace:"\t":"<tab>"}{elseif isset($Exportformat->cFusszeile)}{$Exportformat->cFusszeile}{/if}</textarea>
                    </p>
                {else}
                    <input name="cContent" type="hidden" value="{if isset($Exportformat->cContent)}{$Exportformat->cContent}{/if}" />
                {/if}
            </div>
        </div>
        <div class="card settings">
            <div class="card-header">
                <div class="subheading1">{__('settings')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                {foreach $Conf as $cnf}
                    {if $cnf->cConf === 'Y'}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="{$cnf->cWertName}">{$cnf->cName}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {if $cnf->cInputTyp === 'selectbox'}
                                    <select class="custom-select" name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                                        {foreach $cnf->ConfWerte as $wert}
                                            <option value="{$wert->cWert}" {if isset($cnf->gesetzterWert) && $cnf->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                        {/foreach}
                                    </select>
                                {else}
                                    <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" tabindex="3" />
                                {/if}
                            </div>
                            {if $cnf->cBeschreibung}
                                <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                    {getHelpDesc cDesc=$cnf->cBeschreibung}
                                </div>
                            {/if}
                        </div>
                    {else}
                        <h3 style="text-align:center;">{$cnf->cName}</h3>
                    {/if}
                {/foreach}
            </div>
        </div>
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <a class="btn btn-outline-primary btn-block" href="exportformate.php">
                        {__('cancelWithIcon')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <button type="submit" class="btn btn-primary btn-block" value="{if !isset($Exportformat->kExportformat) || !$Exportformat->kExportformat}{__('newExportformatSave')}{else}{__('modifyExportformatSave')}{/if}">
                        <i class="fa fa-save"></i> {if !isset($Exportformat->kExportformat) || !$Exportformat->kExportformat}{__('newExportformatSave')}{else}{__('modifyExportformatSave')}{/if}
                    </button>
                </div>
            </div>
        </div>
    </form>

    {if isset($Exportformat->kExportformat)}
        {getRevisions type='export' key=$Exportformat->kExportformat show=['cContent','cKopfzeile','cFusszeile'] data=$Exportformat}
    {/if}
</div>
<script>
    {literal}
    function validateTemplateSyntax(tplID) {
        simpleAjaxCall('io.php', {
            jtl_token: JTL_TOKEN,
            io : JSON.stringify({
                name: 'exportformatSyntaxCheck',
                params : [tplID]
            })
        }, function (result) {
            if (result.message && result.message !== '') {
                createNotify({
                    title: '{/literal}{__('smartySyntaxError')}{literal}',
                    message: result.message,
                }, {
                    allow_dismiss: true,
                    type: 'danger',
                    delay: 0
                });
            } else {
                createNotify({
                    title: '{/literal}{__('Check syntax')}{literal}',
                    message: '{/literal}{__('Smarty syntax ok')}{literal}',
                }, {
                    allow_dismiss: true,
                    type: 'success',
                    delay: 1500
                });
            }
        }, function (result) {
            if (result.statusText) {
                let msg = result.statusText;
                if (result.responseJSON && result.responseJSON.error.message !== '') {
                    msg += '<br>' + result.responseJSON.error.message;
                }
                createNotify({
                    title: '{/literal}{__('Syntax check fail')}{literal}',
                    message: msg,
                }, {
                    allow_dismiss: true,
                    type: 'warning',
                    delay: 0
                });
            }
        }, undefined, true);
    }
    {/literal}{if isset($Exportformat->kExportformat)}{literal}
    validateTemplateSyntax({/literal}{$Exportformat->kExportformat}{literal});
    {/literal}{/if}{literal}
    {/literal}
</script>
