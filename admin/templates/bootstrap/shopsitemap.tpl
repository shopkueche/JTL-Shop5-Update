{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('shopsitemap') cBeschreibung=__('shopsitemapDesc') cDokuURL=__('shopsitemapURL')}
<div id="content">
    <form name="einstellen" method="post" action="shopsitemap.php" id="einstellen">
        {$jtl_token}
        <input type="hidden" name="speichern" value="1" />
        <div id="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('settings')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    {foreach $oConfig_arr as $cnf}
                        {if $cnf->cConf === 'Y'}
                            <div class="form-group form-row align-items-center item{if isset($cnf->kEinstellungenConf) && isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="{$cnf->cWertName}">{$cnf->cName}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {if $cnf->cInputTyp === 'selectbox'}
                                        <select class="custom-select" name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                                            {foreach $cnf->ConfWerte as $wert}
                                                <option value="{$wert->cWert}" {if $cnf->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                            {/foreach}
                                        </select>
                                {elseif $cnf->cInputTyp === 'pass'}
                                    <input class="form-control" type="password" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                                {else}
                                    <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                                {/if}
                                </div>
                                {if $cnf->cBeschreibung}
                                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                        {getHelpDesc cDesc=$cnf->cBeschreibung cID=$cnf->kEinstellungenConf}
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    {/foreach}
                </div>
                <div class="save-wrapper card-footer">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
