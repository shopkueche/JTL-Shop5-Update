{assign var=template value=__('template')}
{assign var=modify value=__('modify')}
{include file='tpl_inc/seite_header.tpl'
    cTitel=$template|cat: ' - '|cat:{__('name_'|cat:$mailTemplate->getModuleID())}|cat: ' - '|cat:$modify
    cBeschreibung=__('emailTemplateModifyHint')}
<div id="content">
    <form name="vorlagen_aendern" method="post" action="emailvorlagen.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="Aendern" value="1" />
        {if $mailTemplate->getPluginID() > 0}
            <input type="hidden" name="kPlugin" value="{$mailTemplate->getPluginID()}" />
        {/if}
        <input type="hidden" name="kEmailvorlage" value="{$mailTemplate->getID()}" />
        <div id="settings" class="settings">
            {if $mailTemplate->getModuleID() !== 'core_jtl_anbieterkennzeichnung'}
                <div class="settings card">
                    <div class="card-header">
                        <div class="subheading1">{__('settings')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cAktiv">{__('emailActive')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="cAktiv" id="cAktiv" class="custom-select">
                                    <option value="Y"{if $mailTemplate->getActive()} selected{/if}>
                                        {__('yes')}
                                    </option>
                                    <option value="N"{if !$mailTemplate->getActive()} selected{/if}>
                                        {__('no')}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cEmailOut">{__('emailOut')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" id="cEmailOut" name="cEmailOut" type="text" value="{if isset($mailConfig.cEmailOut)}{$mailConfig.cEmailOut|escape}{/if}" />
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cEmailSenderName">{__('emailSenderName')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" id="cEmailSenderName" name="cEmailSenderName" type="text" value="{if isset($mailConfig.cEmailSenderName)}{$mailConfig.cEmailSenderName|escape}{/if}" />
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cEmailCopyTo">{__('emailCopyTo')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" id="cEmailCopyTo" name="cEmailCopyTo" type="text" value="{if isset($mailConfig.cEmailCopyTo)}{$mailConfig.cEmailCopyTo|escape}{/if}" />
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('multipleDividedColon')}</div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cMailTyp">{__('mailType')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="cMailTyp" id="cMailTyp" class="custom-select">
                                    <option value="text/html" {if $mailTemplate->getType() === 'text/html'}selected{/if}>
                                        {__('textHtml')}
                                    </option>
                                    <option value="text" {if $mailTemplate->getType() === 'text'}selected{/if}>{__('text')}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nAKZ">{__('emailAddAKZ')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="nAKZ" name="nAKZ" class="custom-select">
                                    <option value="0"{if $mailTemplate->getShowAKZ() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowAKZ() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nAFK">{__('emailAddAGB')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="nAFK" name="nAGB" class="custom-select">
                                    <option value="0"{if $mailTemplate->getShowAGB() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowAGB() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nWRB">{__('emailAddWRB')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="nWRB" name="nWRB" class="custom-select">
                                    <option value="0"{if $mailTemplate->getShowWRB() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowWRB() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nWRBForm">{__('emailAddWRBForm')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="nWRBForm" name="nWRBForm" class="custom-select">
                                    <option value="0"{if $mailTemplate->getShowWRBForm() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowWRBForm() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nDSE">{__('emailAddDSE')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="nDSE" name="nDSE" class="custom-select">
                                    <option value="0"{if $mailTemplate->getShowDSE() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowDSE() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            {else}
                <input type="hidden" name="cEmailActive" value="Y" />
                <input type="hidden" name="cMailTyp" value="text/html" />
            {/if}
            <div class="box_info card">
                <div class="card-header">
                    <div class="subheading1">{__('placeholder')} ({__('example')})</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <code>
                        <span class="elem">
                            <span class="name">{ldelim}$Kunde->cAnrede{rdelim}</span><br />
                            <span class="for">{__('maleShort')}</span><br />
                        </span>
                        <span class="elem">
                            <span class="name">{ldelim}$Kunde->cAnredeLocalized{rdelim}</span><br />
                            <span class="for">{__('mister')}</span><br />
                        </span>
                        <span class="elem">
                            <span class="name">{ldelim}$Kunde->cVorname{rdelim}</span><br />
                            <span class="for">{__('firstNameStub')}</span><br />
                        </span>
                        <span class="elem">
                            <span class="name">{ldelim}$Kunde->cNachname{rdelim}</span><br />
                            <span class="for">{__('lastNameStub')}</span><br />
                        </span>
                        <span class="elem">
                            <span class="name">{ldelim}$Firma->cName{rdelim}</span><br />
                            <span class="for">{__('companyStub')}</span><br />
                        </span>
                    </code>
                </div>
            </div>
            {foreach $availableLanguages as $availableLanguage}
                <div class="box_info card">
                    {assign var=kSprache value=$availableLanguage->getId()}
                    <div class="card-header">
                        <div class="subheading1">{__('content')} {$availableLanguage->getLocalizedName()}</div>
                        <hr class="mb-n2">
                    </div>
                    <div class="card-body">
                        {if $mailTemplate->getModuleID() !== 'core_jtl_anbieterkennzeichnung'}
                            <div class="form-row">
                                <label class="col-sm-auto col-form-label" for="cBetreff_{$kSprache}">{__('subject')}:</label>
                                <div class="cols-12 col-md-6">
                                    <input class="form-control" type="text" name="cBetreff_{$kSprache}" id="cBetreff_{$kSprache}"
                                           value="{$mailTemplate->getSubject($kSprache)}" tabindex="1" />
                                </div>
                            </div>
                        {/if}
                        <div class="mt-5">
                            <label class="" for="cContentHtml_{$kSprache}">{__('mailHtml')}:</label>
                            <textarea class="codemirror smarty" id="cContentHtml_{$kSprache}" name="cContentHtml_{$kSprache}"
                                           rows="20">{$mailTemplate->getHTML($kSprache)}</textarea>
                        </div>
                        <div class="my-5">
                            <label class="" for="cContentText_{$kSprache}">{__('mailText')}:</label>
                            <textarea class="codemirror smarty" id="cContentText_{$kSprache}" name="cContentText_{$kSprache}"
                                      rows="20">{$mailTemplate->getText($kSprache)}</textarea>
                        </div>
                        {if $mailTemplate->getAttachments($kSprache)|@count > 0}
                            <div class="row mt-4">
                                <div class="col-sm-auto col-form-label">{__('currentFiles')}:</div>
                                <div class="col-sm-auto">
                                    <a href="emailvorlagen.php?kEmailvorlage={$mailTemplate->getID()}&kS={$kSprache}&a=pdfloeschen&token={$smarty.session.jtl_token}{if $mailTemplate->getPluginID() > 0}&kPlugin={$mailTemplate->getPluginID()}{/if}"
                                       class="btn btn-danger">
                                        {__('deleteAll')}
                                    </a>
                                </div>
                            </div>
                            <div class="row mb-4 mt-2">
                                <div class="col-sm-auto">{__('files')}:</div>
                            {foreach $mailTemplate->getAttachmentNames($kSprache) as $cPDF}
                                {assign var=i value=$cPDF@iteration-1}
                                <div class="col-sm-auto">
                                    <span class="badge badge-info p-2 my-1">{$cPDF}.pdf</span>
                                </div>
                            {/foreach}
                            </div>
                        {/if}
                        {if $mailTemplate->getModuleID() !== 'core_jtl_anbieterkennzeichnung'}
                            {$attachments = $mailTemplate->getAttachmentNames($kSprache)}
                            {section name=anhaenge loop=4 start=1 step=1}
                                <hr class="my-2">
                                <div class="mb-4">
                                    <label for="cPDFS_{$smarty.section.anhaenge.index}_{$kSprache}">{__('pdf')} {$smarty.section.anhaenge.index}:</label>
                                    <div class="for">
                                        {math equation="x-y" x=$smarty.section.anhaenge.index y=1 assign=loopdekr}
                                        <div class="form-row mb-2{if count($cFehlerAnhang_arr) > 0 && isset($cFehlerAnhang_arr[$kSprache][$smarty.section.anhaenge.index]) && $cFehlerAnhang_arr[$kSprache][$smarty.section.anhaenge.index] == 1} form-error{/if}">
                                            <div class="col-sm-auto col-form-label">
                                                <label for="cPDFNames_{$smarty.section.anhaenge.index}_{$kSprache}">{__('filename')}:</label>
                                            </div>
                                            <div class="col-sm-auto">
                                                <input id="cPDFNames_{$smarty.section.anhaenge.index}_{$kSprache}"
                                                   name="cPDFNames_{$kSprache}[]"
                                                   type="text"
                                                   value="{if isset($attachments[$loopdekr + 1])}{$attachments[$loopdekr + 1]}{/if}"
                                                   class="form-control"
                                                   size="50"/>
                                            </div>
                                        </div>
                                        {include file='tpl_inc/fileupload.tpl'
                                            fileID="cPDFS_{$smarty.section.anhaenge.index}_{$kSprache}"
                                            fileName="cPDFS_{$kSprache}[]"
                                            fileAllowedExtensions="['pdf']"
                                            fileShowRemove=true
                                            fileMaxSize=2097152
                                            filePreview=false
                                            fileErrorMsg=true
                                        }
                                    </div>
                                </div>
                            {/section}
                        {/if}
                        </div>
                    </div>
            {/foreach}
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a href="emailvorlagen.php" title="{__('cancel')}" class="btn btn-outline-primary btn-block">
                            {__('cancelWithIcon')}
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" name="continue" value="1" class="btn btn-outline-primary btn-block">
                            <i class="fal fa-save"></i> {__('saveAndContinue')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" name="continue" value="0" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {if $mailTemplate->getID() > 0}
        {getRevisions type='mail' key=$mailTemplate->getID() show=['cContentText','cContentHtml'] secondary=true data=$mailTemplate->viewCompat()}
    {/if}
</div>
<script>
    {literal}
    function validateTemplateSyntax(tplID) {
        simpleAjaxCall('io.php', {
            jtl_token: JTL_TOKEN,
            io : JSON.stringify({
                name: 'mailvorlageSyntaxCheck',
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
            }
            if (result.result && typeof result.result === 'object') {
                let ok = true;
                for (var res in result.result) {
                    var lang = result.result[res];
                    if (lang.message && lang.state && lang.state !== 'ok') {
                        ok = false;
                        createNotify({
                            title: res + ': {/literal}{__('smartySyntaxError')}{literal}',
                            message: lang.message,
                        }, {
                            allow_dismiss: true,
                            type: 'danger',
                            delay: 0
                        });
                    }
                }
                if (ok) {
                    createNotify({
                        title: '{/literal}{__('Check syntax')}{literal}',
                        message: '{/literal}{__('Smarty syntax ok')}{literal}',
                    }, {
                        allow_dismiss: true,
                        type: 'success',
                        delay: 1500
                    });
                }
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
    validateTemplateSyntax({/literal}{$mailTemplate->getID()}{literal});
    {/literal}
</script>
