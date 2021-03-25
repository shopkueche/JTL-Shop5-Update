{include file='tpl_inc/seite_header.tpl' cTitel=__('agbwrb') cBeschreibung=__('agbWrbInfo')}
<div id="content">
    <div class="ocontainer">
        <form name="umfrage" method="post" action="agbwrb.php">
            {$jtl_token}
            <input type="hidden" name="agbwrb" value="1" />
            <input type="hidden" name="agbwrb_editieren_speichern" value="1" />
            <input type="hidden" name="kKundengruppe" value="{if isset($kKundengruppe)}{$kKundengruppe}{/if}" />

            {if isset($oAGBWRB->kText) && $oAGBWRB->kText > 0}
                <input type="hidden" name="kText" value="{if isset($oAGBWRB->kText)}{$oAGBWRB->kText}{/if}" />
            {/if}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('agbwrb')} {foreach $availableLanguages as $language}{if $language->getId() === $languageID}({$language->getLocalizedName()}){/if}{/foreach}{if isset($kKundengruppe)} {__('forCustomerGroup')} {$kKundengruppe} {__('edit')}{/if}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cAGBContentText">{__('agb')} ({__('text')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cAGBContentText" class="form-control" name="cAGBContentText" rows="15" cols="60">{if isset($oAGBWRB->cAGBContentText)}{$oAGBWRB->cAGBContentText}{/if}</textarea>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cAGBContentHtml">{__('agb')} ({__('html')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cAGBContentHtml" name="cAGBContentHtml" class="form-control ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cAGBContentHtml)}{$oAGBWRB->cAGBContentHtml}{/if}</textarea>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cWRBContentText">{__('wrb')} ({__('text')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cWRBContentText" class="form-control" name="cWRBContentText" rows="15" cols="60">{if isset($oAGBWRB->cWRBContentText)}{$oAGBWRB->cWRBContentText}{/if}</textarea>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cWRBContentHtml">{__('wrb')} ({__('html')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cWRBContentHtml" name="cWRBContentHtml" class="form-control ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cWRBContentHtml)}{$oAGBWRB->cWRBContentHtml}{/if}</textarea>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cWRBFormContentText">{__('wrbform')} ({__('text')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cWRBFormContentText" class="form-control" name="cWRBFormContentText" rows="15" cols="60">{if isset($oAGBWRB->cWRBFormContentText)}{$oAGBWRB->cWRBFormContentText}{/if}</textarea>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cWRBFormContentHtml">{__('wrbform')} ({__('html')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cWRBFormContentHtml" name="cWRBFormContentHtml" class="form-control ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cWRBFormContentHtml)}{$oAGBWRB->cWRBFormContentHtml}{/if}</textarea>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cDSEContentText">{__('dse')} ({__('text')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cDSEContentText" class="form-control" name="cDSEContentText" rows="15" cols="60">{if isset($oAGBWRB->cDSEContentText)}{$oAGBWRB->cDSEContentText}{/if}</textarea>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-3 col-form-label text-sm-right" for="cDSEContentHtml">{__('dse')} ({__('html')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <textarea id="cDSEContentHtml" name="cDSEContentHtml" class="form-control ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cDSEContentHtml)}{$oAGBWRB->cDSEContentHtml}{/if}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a class="btn btn-outline-primary btn-block" href="agbwrb.php">
                                {__('cancelWithIcon')}
                            </a>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="agbwrbsubmit" type="submit" value="{__('save')}" class="btn btn-primary btn-block">{__('saveWithIcon')}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
