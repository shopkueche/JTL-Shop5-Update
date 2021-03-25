{include file='tpl_inc/seite_header.tpl'
    cTitel=__('pluginverwaltungLicenceKeyInput')|cat:': '|cat:$oPlugin->getMeta()->getName()
    cBeschreibung=__('pluginverwaltungDesc')
}
<div id="content">
    <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
        {$jtl_token}
        <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
        <input type="hidden" name="lizenzkeyadd" value="1" />
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />

        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('pluginverwaltungLicenceKeyInput')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cKey">{__('pluginverwaltungLicenceKey')}:</label>
                    <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input id="cKey" placeholder="{__('pluginverwaltungLicenceKey')}" class="form-control" name="cKey" type="text" value="{if isset($oPlugin->cLizenz)}{$oPlugin->cLizenz}{/if}" />
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
