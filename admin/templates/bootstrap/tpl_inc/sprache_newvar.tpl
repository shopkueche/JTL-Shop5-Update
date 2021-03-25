{include file='tpl_inc/seite_header.tpl' cTitel=__('lang') cBeschreibung=__('langDesc') cDokuURL=__('langURL')}
<div id="content">
    <div class="card settings">
        <div class="card-header">
            <div class="subheading1">{__('newLangVar')}</div>
            <hr class="mb-n3">
        </div>
        <form action="sprache.php" method="post">
            {$jtl_token}
            <input type="hidden" name="tab" value="{$tab}">
            <div class="card-body">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kSprachsektion">{__('langSection')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select class="custom-select" name="kSprachsektion" id="kSprachsektion">
                            {foreach $oSektion_arr as $oSektion}
                                <option value="{$oSektion->kSprachsektion}"
                                        {if $oVariable->kSprachsektion === (int)$oSektion->kSprachsektion}selected{/if}>
                                    {$oSektion->cName}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('variableName')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input type="text" class="form-control" name="cName" id="cName" value="{$oVariable->cName}">
                    </div>
                </div>
                {foreach $oSprache_arr as $language}
                    {assign var=langCode value=$language->getIso()}
                    {if isset($oVariable->cWertAlt_arr[$langCode])}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="bOverwrite_{$langCode}_yes">
                                <input type="radio" id="bOverwrite_{$langCode}_yes"
                                       name="bOverwrite_arr[{$langCode}]" value="1">
                                {$language->getLocalizedName()} ({__('new')}):
                            </label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="text" class="form-control" name="cWert_arr[{$langCode}]"
                                       id="cWert_{$langCode}" value="{if !empty($oVariable->cWert_arr[$langCode])}{$oVariable->cWert_arr[$langCode]}{/if}">
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="bOverwrite_{$langCode}_no">
                                    <input type="radio" id="bOverwrite_{$langCode}_no"
                                           name="bOverwrite_arr[{$langCode}]" value="0" checked>
                                    {$language->getLocalizedName()} ({__('current')}):
                                </label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="text" class="form-control" name="cWertAlt_arr[{$langCode}]" disabled
                                       id="cWertAlt_{$langCode}"
                                       value="{if !empty($oVariable->cWertAlt_arr[$langCode])}{$oVariable->cWertAlt_arr[$langCode]}{/if}">
                            </div>
                        </div>
                    {else}
                        <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="cWert_{$langCode}">
                                    {$language->getLocalizedName()}:
                                </label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="text" class="form-control" name="cWert_arr[{$langCode}]"
                                       id="cWert_{$langCode}" value="{$oVariable->cWert_arr[$langCode]|default:''}">
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a href="sprache.php?tab={$tab}" class="btn btn-outline-primary btn-block">{__('cancelWithIcon')}</a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" class="btn btn-primary btn-block" name="action" value="savevar">
                            <i class="fa fa-save"></i>
                            {__('save')}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
