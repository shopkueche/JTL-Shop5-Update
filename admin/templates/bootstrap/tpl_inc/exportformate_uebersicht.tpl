{include file='tpl_inc/seite_header.tpl' cTitel=__('exportformats') cBeschreibung=__('exportformatsDesc') cDokuURL=__('exportformatsURL')}
<div id="content">
    <script type="text/javascript" src="{$templateBaseURL}js/jquery.progressbar.js"></script>
    <script type="text/javascript">
        var url     = "{$adminURL}/exportformate.php",
            token   = "{$smarty.session.jtl_token}",
            running = [],
            imgPath = "{$templateBaseURL}gfx/jquery";
        {literal}
        $(function () {
            $('.extract_async').on('click', function (el) {
                init_export(parseInt(el.currentTarget.dataset.exportid, 10));
                return false;
            });
            $('#exportall').on('click', function () {
                $('.extract_async').trigger('click');
                return false;
            });
        });

        function init_export(id) {
            if (running.indexOf(id) !== -1) {
                return false;
            }
            running.push(id);
            show_export_info({kExportformat: id, bFirst: true, nMax: 0, nCurrent: 0});
            $.getJSON(url, {token: token, action: 'export', kExportformat: id, ajax: '1'}, function (cb) {
                do_export(cb);
            });
            return false;
        }

        function do_export(cb) {
            if (typeof cb !== 'object') {
                error_export();
            } else if (cb.bFinished) {
                finish_export(cb);
            } else {
                show_export_info(cb);
                $.getJSON(cb.cURL, {token: token, action: 'export', e: cb.kExportqueue, back: 'admin', ajax: '1', max: cb.nMax}, function (cb) {
                    do_export(cb);
                });
            }
        }

        function error_export(cb) {
            alert('{/literal}{__('errorExport')}{literal}');
        }

        function show_export_info(cb) {
            var elem = '#progress' + cb.kExportformat;
            $(elem).find('p').hide();
            $(elem).find('div').fadeIn();
            $(elem).find('div').progressBar(cb.nCurrent, {
                max:          cb.nMax,
                textFormat:   'fraction',
                steps:        cb.bFirst ? 0 : 20,
                stepDuration: cb.bFirst ? 0 : 20,
                boxImage:     imgPath + '/progressbar.gif',
                barImage:     {
                    0: imgPath + '/progressbg_red.gif',
                    30: imgPath + '/progressbg_orange.gif',
                    50: imgPath + '/progressbg_yellow.gif',
                    70: imgPath + '/progressbg_green.gif'
                }
            });
        }

        function finish_export(cb) {
            var elem = '#progress' + cb.kExportformat,
                idx  = running.indexOf(cb.kExportformat);
            if (idx > -1) {
                running.splice(idx, 1);
            }
            $(elem).find('div').fadeOut(250, function () {
                $('#error-msg-' + cb.kExportformat).remove();
                var text  = $(elem).find('p').html(),
                    error = '';
                if (cb.errorMessage.length > 0) {
                    error = '<span class="red" id="error-msg-' + cb.kExportformat + '"><br>' + cb.errorMessage + '</span>';
                }
                $(elem).find('p').html(text).append(error).fadeIn(1000);
            });
        }
        {/literal}
    </script>

    <div class="card">
        <div class="card-header">
            <div class="subheading1">{__('availableFormats')}</div>
            <hr class="mb-n3">
        </div>
        <div class="table-responsive card-body">
            <table class="table table-align-top">
                <thead>
                <tr>
                    <th class="text-left">{__('name')}</th>
                    <th class="text-left" style="width:320px">{__('filename')}</th>
                    <th class="text-center">{__('language')}</th>
                    <th class="text-center">{__('currency')}</th>
                    <th class="text-center">{__('customerGroup')}</th>
                    <th class="text-center">{__('lastModified')}</th>
                    <th class="text-center">{__('syntax')}</th>
                    <th class="text-center" style="width:200px">{__('actions')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $exportformate as $exportformat}
                    {if $exportformat->nSpecial === 0}
                        <tr>
                            <td class="text-left"> {$exportformat->cName}</td>
                            <td class="text-left" id="progress{$exportformat->kExportformat}">
                                <p>{$exportformat->cDateiname}</p>
                                <div></div>
                            </td>
                            <td class="text-center">{$exportformat->Sprache->getLocalizedName()}</td>
                            <td class="text-center">{$exportformat->Waehrung->cName}</td>
                            <td class="text-center">{$exportformat->Kundengruppe->cName}</td>
                            <td class="text-center">{if !empty($exportformat->dZuletztErstellt)}{$exportformat->dZuletztErstellt}{else}-{/if}</td>
                            <td class="text-center" id="exFormat_{$exportformat->kExportformat}">
                                {include file='snippets/exportformat_state.tpl' exportformat=$exportformat}
                            </td>
                            <td class="text-center">
                                <form method="post" action="exportformate.php">
                                    {$jtl_token}
                                    <input type="hidden" name="kExportformat" value="{$exportformat->kExportformat}" />
                                    <div class="btn-group">
                                        <button type="button" data-id="{$exportformat->kExportformat}" class="btn btn-link px-1 btn-syntaxcheck" title="{__('Check syntax')}" data-toggle="tooltip" data-placement="top">
                                            <span class="icon-hover">
                                                <span class="fal fa-check"></span>
                                                <span class="fas fa-check"></span>
                                            </span>
                                        </button>
                                        <button name="action" value="delete" class="btn btn-link px-1 remove notext" title="{__('delete')}" onclick="return confirm('{__('sureDeleteFormat')}');" data-toggle="tooltip" data-placement="top">
                                            <span class="icon-hover">
                                                <span class="fal fa-trash-alt"></span>
                                                <span class="fas fa-trash-alt"></span>
                                            </span>
                                        </button>
                                        <button name="action" value="export" class="btn btn-link px-1 extract notext" title="{__('createExportFile')}" data-toggle="tooltip" data-placement="top">
                                            <span class="icon-hover">
                                                <span class="fal fa-plus"></span>
                                                <span class="fas fa-plus"></span>
                                            </span>
                                        </button>
                                        <button name="action" value="download" class="btn btn-link px-1 download notext" title="{__('download')}" data-toggle="tooltip" data-placement="top">
                                            <span class="icon-hover">
                                                <span class="fal fa-download"></span>
                                                <span class="fas fa-download"></span>
                                            </span>
                                        </button>
                                        {if !$exportformat->bPluginContentExtern}
                                            <a href="#" class="btn btn-link px-1 extract_async notext" title="{__('createExportFileAsync')}" data-toggle="tooltip" data-placement="top" data-exportid="{$exportformat->kExportformat}" id="start-export-{$exportformat->kExportformat}">
                                                <span class="icon-hover">
                                                    <span class="fal fa-plus-square"></span>
                                                    <span class="fas fa-plus-square"></span>
                                                </span>
                                            </a>
                                        {/if}
                                        <button name="action" value="edit" class="btn btn-link px-1 edit notext" title="{__('edit')}" data-toggle="tooltip" data-placement="top">
                                            <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
                </tbody>
            </table>
        </div>
        <div class="card-footer save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <a class="btn btn-outline-primary btn-block" href="#" id="syntaxcheckall">
                        <i class="fa fa-check"></i> {__('Check syntax')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <a class="btn btn-outline-primary btn-block" href="#" id="exportall">
                        {__('exportAll')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <a class="btn btn-primary btn-block" href="exportformate.php?neuerExport=1&token={$smarty.session.jtl_token}">
                        <i class="fa fa-share"></i> {__('newExportformat')}
                    </a>
                </div>
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
    function validateExportFormatSyntax(tplID, massCheck) {
        $('#exFormat_' + tplID).html('<span class="fa fa-spinner fa-spin"></span>');
        simpleAjaxCall('io.php', {
            jtl_token: JTL_TOKEN,
            io : JSON.stringify({
                name: 'exportformatSyntaxCheck',
                params : [tplID]
            })
        }, function (result) {
            if (result.state && result.state !== '') {
                $('#exFormat_' + tplID).html(result.state);
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
            } else if (result.result && result.result === 'ok' && !massCheck) {
                createNotify({
                    title: '{/literal}{__('Check syntax')}{literal}',
                    message: '{/literal}{__('Smarty syntax ok')}{literal}',
                }, {
                    allow_dismiss: true,
                    type: 'success',
                    delay: 1500
                });
            }
            updateSyntaxNotify();
        }, function (result) {
            $('#exFormat_' + tplID).html('<span class="label text-warning">{/literal}{__('untested')}{literal}</span>');
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
        validateExportFormatSyntax(doCheckTpl);
    }
    $('.btn-syntaxcheck').on('click', function (e) {
        let id = $(this).data('id');
        if (id) {
            validateExportFormatSyntax(id);
        }
    });
    $('#syntaxcheckall').on('click', function (e) {
        $('.btn-syntaxcheck').each(function (e) {
            let id = $(this).data('id');
            if (id) {
                validateExportFormatSyntax(id, true);
            }
        });

        return false;
    })
    {/literal}
</script>
