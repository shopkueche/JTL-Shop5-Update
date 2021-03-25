{include file='tpl_inc/seite_header.tpl' cTitel=__('emailTemplates') cBeschreibung=__('emailTemplatesHint') cDokuURL=__('emailTemplateURL')}
<div id="content">
    <div class="alert alert-info">
        {__('testmailsGoToEmail')}
        <strong>
            {if $Einstellungen.emails.email_master_absender}
                {$Einstellungen.emails.email_master_absender}
            {else}
                {__('noMasterEmailSpecified')}
            {/if}
        </strong>
    </div>
    {include file='tpl_inc/mailtemplate_list.tpl' heading=__('emailTemplates') mailTemplates=$mailTemplates}
    {include file='tpl_inc/mailtemplate_list.tpl' heading=__('pluginTemplates') mailTemplates=$pluginMailTemplates}
    <div class="save-wrapper">
        <div class="row">
            <div class="ml-auto col-sm-6 col-xl-auto">
                <button type="button" class="btn btn-primary btn-block btn-syntaxcheck-all">
                    <i class="fa fa-check"></i> {__('Check syntax')}
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    {literal}
    function updateSyntaxNotify() {
        if (doNotify) {
            window.clearTimeout(doNotify);
        }
        doNotify = window.setTimeout(function () {
            ioCall('notificationAction', ['refresh'], undefined, undefined, undefined, true);
            doNotify = null;
        }, 1500);
    }
    function validateTemplateSyntax(tplID, massCheck) {
        $('#tplState_' + tplID).html('<span class="fa fa-spinner fa-spin"></span>');
        simpleAjaxCall('io.php', {
            jtl_token: JTL_TOKEN,
            io : JSON.stringify({
                name: 'mailvorlageSyntaxCheck',
                params : [tplID]
            })
        }, function (result) {
            if (result.state && result.state !== '') {
                $('#tplState_' + tplID).html(result.state);
            }
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
                if (ok && !massCheck) {
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
            updateSyntaxNotify();
        }, function (result) {
            $('#tplState_' + tplID).html('<span class="label text-warning">{/literal}{__('untested')}{literal}</span>');
            updateSyntaxNotify();
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
    var doCheckTpl = {/literal}{$checkTemplate}{literal};
    var doNotify = null;
    if (doCheckTpl && doCheckTpl > 0) {
        validateTemplateSyntax(doCheckTpl);
    }
    $('.btn-syntaxcheck').on('click', function (e) {
        let id = $(this).data('id');
        if (id) {
            validateTemplateSyntax(id);
        }
    });
    $('.btn-syntaxcheck-all').on('click', function (e) {
        $('.btn-syntaxcheck').each(function (e) {
            let id = $(this).data('id');
            if (id) {
                validateTemplateSyntax(id, true);
            }
        });
    })
    {/literal}
</script>