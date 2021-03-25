{assign var=bForceFluid value=$bForceFluid|default:false}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{__('shopTitle')}</title>
    {assign var=urlPostfix value='?v='|cat:$adminTplVersion}
    <link type="image/x-icon" href="{$faviconAdminURL}" rel="icon">
    {$admin_css}
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}lib/codemirror.css{$urlPostfix}">
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/hint/show-hint.css{$urlPostfix}">
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/display/fullscreen.css{$urlPostfix}">
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/scroll/simplescrollbars.css{$urlPostfix}">
    {$admin_js}
    <script src="{$PFAD_CKEDITOR}ckeditor.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}lib/codemirror.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}addon/hint/show-hint.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}addon/hint/sql-hint.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}addon/scroll/simplescrollbars.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}addon/display/fullscreen.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/css/css.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/javascript/javascript.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/xml/xml.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/php/php.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/htmlmixed/htmlmixed.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/smarty/smarty.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/smartymixed/smartymixed.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/sql/sql.js{$urlPostfix}"></script>
    <script src="{$templateBaseURL}js/codemirror_init.js{$urlPostfix}"></script>
    <script>
        var bootstrapButton = $.fn.button.noConflict();
        $.fn.bootstrapBtn = bootstrapButton;
        setJtlToken('{$smarty.session.jtl_token}');

        function switchAdminLang(tag)
        {
            event.target.href = `{strip}
                benutzerverwaltung.php
                ?token={$smarty.session.jtl_token}
                &action=quick_change_language
                &language=` + tag + `
                &referer=` +  encodeURIComponent(window.location.href){/strip};
        }
    </script>

    <script type="text/javascript"
            src="{$templateBaseURL}js/fileinput/locales/{$language|mb_substr:0:2}.js"></script>
    <script type="module" src="{$templateBaseURL}js/app/app.js"></script>
    {include file='snippets/selectpicker.tpl'}
</head>
<body>
{if $account !== false && isset($smarty.session.loginIsValid) && $smarty.session.loginIsValid === true}
    {getCurrentPage assign='currentPage'}
    <div class="spinner"></div>
    <div id="page-wrapper" class="backend-wrapper hidden disable-transitions{if $currentPage === 'index' || $currentPage === 'status'} dashboard{/if}">
        {if !$hasPendingUpdates && $wizardDone}
            {include file='tpl_inc/backend_sidebar.tpl'}
        {/if}
        <div class="backend-main {if !$hasPendingUpdates && $wizardDone}sidebar-offset{/if}">
            {if $smarty.const.SAFE_MODE}
            <div class="alert alert-warning fade show" role="alert">
                <i class="fal fa-exclamation-triangle mr-2"></i>
                {__('Safe mode enabled.')}
                <a href="./?safemode=off" class="btn btn-light"><span class="fas fa-exclamation-circle mr-0 mr-lg-2"></span><span>{__('deactivate')}</span></a>
            </div>
            {/if}
            <div id="topbar" class="backend-navbar row mx-0 align-items-center topbar flex-nowrap">
                {if !$hasPendingUpdates && $wizardDone}
                <div class="col search px-0 px-md-3">
                    {include file='tpl_inc/backend_search.tpl'}
                </div>
                {/if}
                <div class="col-auto ml-auto px-2">
                    <ul class="nav align-items-center">
                        {if !$hasPendingUpdates && $wizardDone}
                            <li class="nav-item dropdown mr-md-3" id="favs-drop">
                                {include file="tpl_inc/favs_drop.tpl"}
                            </li>
                            <li class="nav-item dropdown fa-lg">
                                <a href="#" class="nav-link text-dark-gray px-2" data-toggle="dropdown">
                                    <span class="fal fa-map-marker-question fa-fw"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <span class="dropdown-header">{__('helpCenterHeader')}</span>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="https://jtl-url.de/shopschritte" target="_blank" rel="noopener">
                                        {__('firstSteps')}
                                    </a>
                                    <a class="dropdown-item" href="https://jtl-url.de/0762z" target="_blank" rel="noopener">
                                        {__('jtlGuide')}
                                    </a>
                                    <a class="dropdown-item" href="https://forum.jtl-software.de" target="_blank" rel="noopener">
                                        {__('jtlForum')}
                                    </a>
                                    <a class="dropdown-item" href="https://www.jtl-software.de/Training" target="_blank" rel="noopener">
                                        {__('training')}
                                    </a>
                                    <a class="dropdown-item" href="https://www.jtl-software.de/Servicepartner" target="_blank" rel="noopener">
                                        {__('servicePartners')}
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item dropdown fa-lg" id="notify-drop">{include file="tpl_inc/notify_drop.tpl"}</li>
                            <li class="nav-item dropdown fa-lg" id="updates-drop">{include file="tpl_inc/updates_drop.tpl"}</li>
                        {/if}
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle parent btn-toggle" data-toggle="dropdown">
                                <i class="fal fa-language d-sm-none"></i> <span class="d-sm-block d-none">{$languageName}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                {foreach $languages as $tag => $langName}
                                    {if $language !== $tag}
                                        <a class="dropdown-item" onclick="switchAdminLang('{$tag}')" href="#">
                                            {$langName}
                                        </a>
                                    {/if}
                                {/foreach}
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-auto border-left border-dark-gray px-0 px-md-3">
                    <div class="dropdown avatar">
                        <button class="btn btn-link text-decoration-none dropdown-toggle p-0" data-toggle="dropdown">
                            <img src="{getAvatar account=$account}" class="img-circle">
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item link-shop" href="{$URL_SHOP}?fromAdmin=yes" title="{__('goShop')}" target="_blank">
                                <i class="fa fa-shopping-cart"></i> {__('goShop')}
                            </a>
                            <a class="dropdown-item link-logout" href="logout.php?token={$smarty.session.jtl_token}"
                               title="{__('logout')}">
                                <i class="fa fa-sign-out"></i> {__('logout')}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="opaque-background"></div>
            </div>
            {if !$hasPendingUpdates && $expiredLicenses->count() > 0}
                <div class="modal fade in" id="expiredLicensesNotice" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">{__('Licensing problem detected')}</h5>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-2"><i class="fa fa-exclamation-triangle" style="font-size: 8em; padding-bottom:10px; color: red;"></i></div>
                                    <div class="col-md-10 ml-auto">
                                        <strong>{__('No valid licence found for the following installed and active extensions:')}</strong>
                                        {form id="plugins-disable-form"}
                                            <input type="hidden" name="action" value="disable-expired-plugins">
                                            <ul>
                                                {$hasPlugin = false}
                                                {$hasTemplate = false}
                                                {foreach $expiredLicenses as $license}
                                                    {if $license->getType() === 'plugin'}
                                                        {$hasPlugin = true}
                                                    {elseif $license->getType() === 'template'}
                                                        {$hasTemplate = true}
                                                    {/if}
                                                    <li>{$license->getName()}</li>
                                                    <input type="hidden" name="pluginID[]" value="{$license->getReferencedItem()->getInternalID()}">
                                                {/foreach}
                                            </ul>
                                        {/form}
                                    </div>
                                </div>
                                <div class="alert alert-secondary" role="alert">
                                    <p><strong>{__('Possible reasons:')}</strong></p>
                                    <ul class="small">
                                        <li>{__('The extension was obtained from a different source than the JTL-Extension Store')}</li>
                                        <li>{__('The licence is not bound to this shop yet (check licence in "My purchases")')}</li>
                                        <li>{__('The licence is bound to a different customer account that is not connected to this shop (check connected account in "My purchases")')}</li>
                                        <li>{__('The manufacturer disabled the licence')}</li>
                                    </ul>
                                </div>
                                <p><strong>{__('Further use of the extension may constitute a licence violation!')}</strong><br>
                                    {__('Please purchase a licence in the JTL-Extension Store or contact the manufacturer of the extension for information on rights of use.')}
                                </p>
                            </div>
                            <div class="modal-footer">
                                <input type="checkbox" id="understood-license-notice">
                                <label for="understood-license-notice">{__('I understood this notice.')}</label>
                                <button type="button" class="btn btn-default" disabled data-dismiss="modal" id="licenseUnderstood">{__('Understood')}</button>
                                {if $hasPlugin === true}
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="licenseDisablePlugins">{__('Disable plugins')}</button>
                                {/if}
                                {if $hasTemplate === true}
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="licenseGotoTemplates">{__('Disable template')}</button>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    $(document).ready(function() {
                        $('#expiredLicensesNotice').modal('show');
                        $('#understood-license-notice').on('click', function (e) {
                            $('#licenseUnderstood').attr('disabled', false);
                        });
                        $('#licenseUnderstood').on('click', function (e) {
                            var newURL = new URL(window.location.href);
                            newURL.searchParams.append('licensenoticeaccepted', 'true');
                            window.location.href = newURL.toString();
                            return true;
                        });
                        $('#licenseDisablePlugins').on('click', function (e) {
                            $('#plugins-disable-form').submit();
                            return true;
                        });
                        $('#licenseGotoTemplates').on('click', function (e) {
                            window.location.href = '{$shopURL}/{$smarty.const.PFAD_ADMIN}shoptemplate.php?licensenoticeaccepted=true';
                            return true;
                        });
                    });
                </script>
            {/if}
            <div class="backend-content" id="content_wrapper">

            {include file='snippets/alert_list.tpl'}
{/if}
