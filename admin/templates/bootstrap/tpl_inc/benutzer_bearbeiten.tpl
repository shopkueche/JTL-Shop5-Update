<script type="text/javascript">
{literal}
$(document).ready(function() {
    $('#tmp_check').bind('click', function() {
        if ($(this).is(':checked')) {
            $('#tmp_date').show();
        } else {
            $('#tmp_date').hide();
        }
    });
    $('#dGueltigBis').datetimepicker({
        showSecond: true,
        timeFormat: 'hh:mm:ss',
        dateFormat: 'dd.mm.yy'
    });

    /** bring the 2FA-canvas in a defined position depending on the state of the 2FA */
    if ('nein' === $('#b2FAauth option:selected').text().toLowerCase()) {
        $('[id$=TwoFAwrapper]').hide();
    } else {
        $('[id$=TwoFAwrapper]').show();
    }

    /** install a "toggle-event-handler" to fold or unfold the 2FA-canvas, via the "Ja/Nein"-select */
    $('[id$=b2FAauth]').on('change', function(e) {
        e.stopImmediatePropagation(); // stop this event during page-load
        if('none' === $('[id$=TwoFAwrapper]').css('display')) {
            $('[id$=TwoFAwrapper]').slideDown();
        } else {
            $('[id$=TwoFAwrapper]').slideUp();
        }
    });

    // avatar upload usability
    $('#useAvatar').on('change', function() {
        var useUploadDetails   = $('#useUploadDetails');
        if($(this).val() === 'N') {
            useUploadDetails.addClass('d-none');
        } else {
            useUploadDetails.removeClass('d-none');
        }
    });
    $('#selectVitaLang').on('change', function () {
        var iso = $('#selectVitaLang option:selected').val();
        $('.iso_wrapper').hide();
        $('#isoVita_' + iso).show();
    });
});
</script>
<style>
    /* CONSIDER: styles ar mandatory for the QR-code! */

    /* a small space arround the whole code (not mandatory) */
    div.qrcode{
        /* margin: 0 5px; */
        margin: 5px
    }

    /* row element */
    div.qrcode > p {
        margin: 0;
        padding: 0;
        height: 5px;
    }

    /* column element(s) */
    div.qrcode > p > b,
    div.qrcode > p > i {
        display: inline-block;
        width: 5px;
        height: 5px;
    }

    /* color of 'on-elements' - "the color of the QR" */
    div.qrcode > p > b {
        background-color: #000;
    }

    /* color of 'off-elements' - "the color of the background" */
    div.qrcode > p > i {
        background-color: #fff;
    }
</style>
{/literal}

{assign var=cTitel value=__('newUserTitle')}
{if isset($oAccount->kAdminlogin) && $oAccount->kAdminlogin > 0}
    {assign var=cTitel value=__('benutzerBearbeiten')}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('benutzerDesc')}
<div id="content">
    <form class="navbar-form" action="benutzerverwaltung.php" method="post" enctype="multipart/form-data">
        {$jtl_token}
        <div id="settings" class="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('general')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="item">
                        <div class="form-group form-row align-items-center{if isset($cError_arr.cName)} form-error{/if}">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('preSurName')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input id="cName" class="form-control" type="text" name="cName" value="{if isset($oAccount->cName)}{$oAccount->cName}{/if}" />
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="form-group form-row align-items-center{if isset($cError_arr.cMail)} form-error{/if}">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cMail">{__('emailAddress')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input id="cMail" class="form-control" type="email" name="cMail" value="{if isset($oAccount->cMail)}{$oAccount->cMail}{/if}" />
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="form-group form-row align-items-center{if isset($cError_arr.kSprache)} error{/if}">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="language">{__('language')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="language" class="custom-select" name="language">
                                    {foreach $languages as $langTag => $langName}
                                        <option value="{$langTag}"
                                                {if isset($oAccount->language) && $oAccount->language === $langTag}
                                                    selected="selected"
                                                {/if}>
                                            {$langName}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('logindata')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="item">
                        <div class="form-group form-row align-items-center{if isset($cError_arr.cLogin)} form-error{/if}">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cLogin">{__('username')}
                                {if isset($cError_arr.cLogin) && $cError_arr.cLogin == 2}
                                    <span class="input-group-addon text-danger" data-html="true" data-toggle="tooltip" data-original-title="{{__('usernameNotAvailable')}|sprintf:{$oAccount->cLogin}}"><i class="fa fa-exclamation-triangle"></i></span>
                                {/if}:
                            </label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input id="cLogin" class="form-control" type="text" name="cLogin" value="{if isset($oAccount->cLogin)}{$oAccount->cLogin}{/if}">
                            </div>
                        </div>
                    </div>

                    <div class="item">
                        <div class="form-group form-row align-items-center{if isset($cError_arr.cPass)} form-error{/if}">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cPass">{__('password')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input id="cPass" class="form-control" type="text" name="cPass" autocomplete="off" />
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                <button type="button" onclick="ioCall('getRandomPassword');return false;"
                                        class="btn btn-link p-0" title="{__('passwordGenerate')}">
                                    <span class="icon-hover">
                                        <span class="fal fa-random"></span>
                                        <span class="fas fa-random"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {if isset($oAccount->kAdminlogingruppe) && $oAccount->kAdminlogingruppe > 1}
                        <div class="item">
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="tmp_check">{__('temporaryAccess')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <span class="input-group-checkbox-wrap">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" class="" type="checkbox" id="tmp_check" name="dGueltigBisAktiv" value="1"{if (isset($oAccount->dGueltigBis) && $oAccount->dGueltigBis !== null)} checked="checked"{/if} />
                                            <label class="custom-control-label" for="tmp_check"></label>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="item"{if !$oAccount->dGueltigBis || $oAccount->dGueltigBis == null} style="display: none;"{/if} id="tmp_date">
                            <div class="form-group form-row align-items-center{if !empty($cError_arr.dGueltigBis)} form-error{/if}">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="dGueltigBis">{__('tillInclusive')}</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" type="text" name="dGueltigBis" value="{if $oAccount->dGueltigBis}{$oAccount->dGueltigBis|date_format:'%d.%m.%Y %H:%M:%S'}{/if}" id="dGueltigBis" />
                                </div>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('twoFactorAuth')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="item">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="b2FAauth">{__('activate')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="b2FAauth" class="custom-select" name="b2FAauth">
                                    <option value="0"{if !isset($oAccount->b2FAauth) || (isset($oAccount->b2FAauth) && (bool)$oAccount->b2FAauth === false)} selected="selected"{/if}>{__('no')}</option>
                                    <option value="1"{if isset($oAccount->b2FAauth) && (bool)$oAccount->b2FAauth === true} selected="selected"{/if}>{__('yes')}</option>
                                </select>
                            </div>
                        </div>

                        {literal}
                        <script>
                            function createNewSecret() {
                                if('' === $('[id$=cLogin]').val()) {
                                    alert('{/literal}{__('errorUsernameMissing')}{literal}');
                                    return(false);
                                }

                                if(confirm('{/literal}{__('warningAuthSecretOverwrite')}{literal}')) {
                                    var userName = $('#cLogin').val();
                                    $('#QRcode').html('<img src="templates/bootstrap/gfx/widgets/ajax-loader.gif">');
                                    ioCall('getNewTwoFA', [userName], function (data) {
                                        // display the new RQ-code
                                        $('#QRcode').html(data.szQRcode);
                                        $('#c2FAsecret').val(data.szSecret);

                                        // toggle code-canvas
                                        if('none' === $('#QRcodeCanvas').css('display')) {
                                            $('#QRcodeCanvas').css('display', 'block');
                                        }
                                    });
                                }
                            }

                            function showEmergencyCodes(action) {
                                var userName = $('#cLogin').val();
                                ioCall('genTwoFAEmergencyCodes', [userName], function (data) {
                                    var iframeHtml = '';

                                    iframeHtml += '<h4>{/literal}{__('shopEmergencyCodes')}{literal}</h4>';
                                    iframeHtml += '{/literal}{__('account')}{literal}: <b>' + data.loginName + '</b><br>';
                                    iframeHtml += '{/literal}{__('shop')}{literal}: <b>' + data.shopName + '</b><br><br>';
                                    iframeHtml += '<pre>';

                                    data.vCodes.forEach(function (code, i) {
                                        iframeHtml += code + ' ';
                                        if (i%2 === 1) {
                                            iframeHtml += '\n';
                                        }
                                    });

                                    iframeHtml += '</pre>';

                                    $('#printframe').contents().find('body')[0].innerHTML = iframeHtml;
                                    $('#EmergencyCodeModal').modal('show');
                                });
                            }

                        </script>
                        {/literal}
                        <div id="TwoFAwrapper" {if isset($cError_arr.c2FAsecret)}class="error"{/if} style="border:1px solid {if isset($cError_arr.c2FAsecret)}red{else}lightgrey{/if};padding:10px;">
                            <div id="QRcodeCanvas" style="display:{if '' !== $QRcodeString }block{else}none{/if}">
                                <div class="alert alert-danger" role="alert">
                                    {__('warningNoPermissionToBackendAfter')}
                                </div>
                                {__('infoScanQR')}<br>
                                <div id="QRcode" class="qrcode">{$QRcodeString}</div><br>
                                <input type="hidden" id="c2FAsecret" name="c2FAsecret" value="{$cKnownSecret}">
                                <br>
                            </div>
                            {* Emergency-Code-Modal  BEGIN *}
                            <div class="modal fade" id="EmergencyCodeModal">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title">{__('emergencyCode')}</h2>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <i class="fal fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div id="EmergencyCodes">
                                                <div class="iframewrapper">
                                                    <iframe src="" id="printframe" name="printframe" frameborder="0" width="100%" height="300" align="middle"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <div class="row">
                                                <div class="ml-auto col-sm-6 col-xl-auto mb-2">
                                                    <button class="btn btn-outline-primary btn-block" type="button" data-dismiss="modal">Schließen</button>
                                                </div>
                                                <div class="col-sm-6 col-xl-auto mb-2">
                                                    <button class="btn btn-outline-primary btn-block" type="button" onclick="printframe.print();">Drucken</button>
                                                </div>
                                                <div class="col-sm-6 col-xl-auto">
                                                    <button class="btn btn-danger btn-block" type="button" onclick="showEmergencyCodes('forceReload');">{__('codeCreateAgain')}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {* Emergency-Code-Modal  END *}
                            {__('clickHereToCreateQR')}<br>
                            <br>
                            <div class="row">
                                <div class="col-sm-auto mb-3">
                                    <button class="btn btn-warning btn-block" type="button" onclick="showEmergencyCodes();">
                                        {__('emergencyCodeCreate')}
                                    </button>
                                </div>
                                <div class="col-sm-auto">
                                    <button class="btn btn-primary btn-block" type="button" onclick="createNewSecret();">
                                        {__('codeCreate')}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            {if !isset($oAccount->kAdminlogingruppe) || (isset($nAdminCount) && !($oAccount->kAdminlogingruppe == 1 && $nAdminCount <= 1))}
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('permissions')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        <div class="item">
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="kAdminlogingruppe">{__('userGroup')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <select id="kAdminlogingruppe" class="custom-select" name="kAdminlogingruppe">
                                        {foreach $oAdminGroup_arr as $oGroup}
                                            <option value="{$oGroup->kAdminlogingruppe}" {if isset($oAccount->kAdminlogingruppe) && $oAccount->kAdminlogingruppe == $oGroup->kAdminlogingruppe}selected="selected"{/if}>
                                                {$oGroup->cGruppe} ({$oGroup->nCount})
                                            </option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {else}
                <input type="hidden" name="kAdminlogingruppe" value="1" />
            {/if}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('personalInformation')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="item">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="useAvatar">{__('avatar')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="form-control custom-select" id="useAvatar" name="extAttribs[useAvatar]">
                                    <option value="N">{__('no')}</option>
                                    <option value="U"{if isset($attribValues.useAvatar) && $attribValues.useAvatar->cAttribValue == 'U'} selected="selected"{/if}>{__('yes')}</option>
                                </select>
                            </div>
                        </div>
                        <div id="useUploadDetails" class="item {if !isset($attribValues.useAvatar) || $attribValues.useAvatar->cAttribValue === 'N'}d-none{/if}">
                            <div class="form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="useAvatarUpload">{__('Image')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    {include file='tpl_inc/fileupload.tpl'
                                        fileID='useAvatarUpload'
                                        fileName='extAttribs[useAvatarUpload]'
                                        fileMaxSize=1000
                                        fileInitialPreview="[
                                            {if isset($attribValues.useAvatar) && $attribValues.useAvatar->cAttribValue === 'U'}
                                            '<img src=\"{$shopURL}/{$attribValues.useAvatarUpload->cAttribValue}\" class=\"preview-image\"/>',
                                            {/if}
                                        ]"
                                        fileInitialPreviewConfig="[
                                            {if isset($attribValues.useAvatar) && $attribValues.useAvatar->cAttribValue === 'U'}
                                            {
                                                caption: '{__('preview')}',
                                                width:   '120px'
                                            }
                                            {/if}
                                        ]"
                                    }
                                </div>
                            </div>
                            <input type="hidden" name="extAttribs[useAvatarUpload]" value="{if isset($attribValues.useAvatarUpload)}{$attribValues.useAvatarUpload->cAttribValue}{/if}" />
                            {if isset($cError_arr.useAvatarUpload)}
                                <span class="input-group-addon error"><i class="fa fa-exclamation-triangle"></i></span>
                            {/if}
                        </div>
                    </div>
                    <div class="item">
                        <label for="useVita">{__('resume')}:</label>
                        <select class="form-control custom-select" id="selectVitaLang">
                            {foreach $availableLanguages as $language}
                                <option value="{$language->cISO}"{if $language->cShopStandard === 'Y'} selected="selected"{/if}>{$language->getLocalizedName()} {if $language->cShopStandard === 'Y'}({__('standard')}){/if}</option>
                            {/foreach}
                        </select>
                        <div class="mt-3">
                            {foreach $availableLanguages as $language}
                                {assign var="cISO" value=$language->cISO}
                                {assign var="useVita_ISO" value="useVita_"|cat:$cISO}
                                <div id="isoVita_{$cISO}" class="iso_wrapper{if $language->cShopStandard != 'Y'} hidden-soft{/if}">
                                    <textarea class="form-control ckeditor" id="useVita_{$cISO}" name="extAttribs[useVita_{$cISO}]" rows="10" cols="40">
                                        {if isset($attribValues.$useVita_ISO)}
                                            {if !empty($attribValues.$useVita_ISO->cAttribText)}
                                                {$attribValues.$useVita_ISO->cAttribText}
                                            {else}
                                                {$attribValues.$useVita_ISO->cAttribValue}
                                            {/if}
                                        {/if}
                                    </textarea>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                    {if !empty($extContent)}
                        {$extContent}
                    {/if}
                </div>
            </div>
        </div>
        <div class="card-footer save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <a class="btn btn-outline-primary btn-block" href="benutzerverwaltung.php">
                        {__('cancelWithIcon')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <input type="hidden" name="action" value="account_edit" />
                    {if isset($oAccount->kAdminlogin) && $oAccount->kAdminlogin > 0}
                        <input type="hidden" name="kAdminlogin" value="{$oAccount->kAdminlogin}" />
                    {/if}
                    <input type="hidden" name="save" value="1" />
                    <button type="submit" class="btn btn-primary btn-block">
                        {__('saveWithIcon')}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
