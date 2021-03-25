<script type="text/javascript">
    {literal}
        function changeWertSelect(currentSelect)
        {
            switch ($(currentSelect).val()) {
                case '0':
                    $('#static-value-input-group').show();
                    break;
                case '1':
                    $('#static-value-input-group').hide();
                    break;
            }
        }
    {/literal}
</script>

{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('kampagneEdit')}
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('kampagneCreate')}
{/if}

<form method="post" action="kampagne.php">
    {$jtl_token}
    <input type="hidden" name="tab" value="uebersicht">
    <input type="hidden" name="erstellen_speichern" value="1">
    {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0}
        <input type="hidden" name="kKampagne" value="{$oKampagne->kKampagne}">
    {/if}
    <div class="card settings">
        <div class="card-body">
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('kampagneName')}:</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <input id="cName" class="form-control" name="cName" type="text"
                           value="{if isset($oKampagne->cName)}{$oKampagne->cName}{/if}"
                            {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if}>
                </div>
            </div>
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-4 col-form-label text-sm-right" for="cParameter">{__('kampagneParam')}:</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <input id="cParameter" class="form-control" name="cParameter" type="text"
                           value="{if isset($oKampagne->cParameter)}{$oKampagne->cParameter}{/if}">
                </div>
                <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('kampagneParamDesc')}</div>
            </div>
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-4 col-form-label text-sm-right" for="cWertSelect">{__('kampagneValueType')}:</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <select name="nDynamisch" class="custom-select combo" id="cWertSelect"
                            onChange="changeWertSelect(this);"
                            {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if}>
                        <option value="0"{if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 0} selected{/if}>{__('fixedValue')}</option>
                        <option value="1"{if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1} selected{/if}>{__('dynamic')}</option>
                    </select>
                </div>
                <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('kampagneValueTypeDesc')}</div>
            </div>
            <div class="form-group form-row align-items-center" id="static-value-input-group">
                <label class="col col-sm-4 col-form-label text-sm-right" for="cWert">{__('kampagneValueStatic')}:</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <input id="cWert" class="form-control" name="cWert" type="text"
                           value="{if isset($oKampagne->cWert)}{$oKampagne->cWert}{/if}"
                           {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if} />
                </div>
                <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('kampagneValueStaticDesc')}</div>
            </div>
            <div class="form-group form-row align-items-center">
                <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('activated')}:</label>
                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                    <select id="nAktiv" name="nAktiv" class="combo custom-select">
                        <option value="0"{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 0} selected{/if}>{__('no')}</option>
                        <option value="1"{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 1} selected{/if}>{__('yes')}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class=row>
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <a href="kampagne.php?tab=uebersicht" class="button btn btn-outline-primary btn-block mb-2">
                        {__('cancelWithIcon')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <button name="submitSave" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                        {__('saveWithIcon')}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>