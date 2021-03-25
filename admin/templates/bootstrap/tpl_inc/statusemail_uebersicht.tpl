{include file='tpl_inc/seite_header.tpl' cTitel=__('statusemail') cBeschreibung=__('statusemailDesc') cDokuURL=__('statusemailURL')}
<div id="content">
    <form name="einstellen" method="post" action="statusemail.php">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <div id="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('settings')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="item form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('statusemailUse')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="custom-select" name="nAktiv" id="nAktiv">
                                <option value="1" {if isset($oStatusemailEinstellungen->nAktiv) && $oStatusemailEinstellungen->nAktiv == 1}selected{/if}>{__('yes')}</option>
                                <option value="0" {if isset($oStatusemailEinstellungen->nAktiv) && $oStatusemailEinstellungen->nAktiv == 0}selected{/if}>{__('no')}</option>
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {getHelpDesc cDesc=__('statusemailUseDesc')}
                        </div>
                    </div>

                    <div class="item form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cEmail">{__('statusemailEmail')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="text" name="cEmail" id="cEmail"
                                   value="{if isset($oStatusemailEinstellungen->cEmail)}{$oStatusemailEinstellungen->cEmail}{/if}"
                                   tabindex="1">
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {getHelpDesc cDesc=__('statusemailEmailDesc')}
                        </div>
                    </div>

                    <div class="item form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cIntervall">{__('statusemailIntervall')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select name="cIntervall_arr[]"
                                    id="cIntervall"
                                    multiple="multiple"
                                    class="selectpicker custom-select">
                                {foreach $oStatusemailEinstellungen->cIntervallMoeglich_arr as $key => $nIntervallMoeglich}
                                    <option value="{$nIntervallMoeglich}"
                                            {if $nIntervallMoeglich|in_array:$oStatusemailEinstellungen->nIntervall_arr}selected{/if}>
                                        {$key}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {getHelpDesc cDesc=__('statusemailIntervallDesc')}
                        </div>
                    </div>

                    <div class="item form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cInhalt">{__('statusemailContent')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select name="cInhalt_arr[]"
                                    id="cInhalt"
                                    multiple="multiple"
                                    class="selectpicker custom-select"
                                    data-live-search="true"
                                    data-actions-box="true"
                                    data-selected-text-format="count > 1"
                                    data-size="7">
                                {foreach $oStatusemailEinstellungen->cInhaltMoeglich_arr as $key => $nInhaltMoeglich}
                                    <option value="{$nInhaltMoeglich}"
                                            {if $nInhaltMoeglich|in_array:$oStatusemailEinstellungen->nInhalt_arr}selected{/if}>
                                        {$key}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            {getHelpDesc cDesc=__('statusemailContentDesc')}
                        </div>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button type="submit" class="btn btn-outline-primary btn-block" name="action" value="sendnow">
                                <i class="far fa-envelope"></i> {__('sendEmail')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button type="submit" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>