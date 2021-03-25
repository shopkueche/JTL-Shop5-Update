{include file='tpl_inc/seite_header.tpl' cTitel=__('plz_ort_import') cBeschreibung=__('plz_ort_importDesc')}
<div id="content">
    <div class="card">
        <form id="importForm" action="/plz_ort_import.php">
            {$jtl_token}
            <div class="card-header">
                <div class="subheading1">{__('plz_ort_available')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                {include file='tpl_inc/plz_ort_import_index_list.tpl'}
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a href="#" class="btn btn-primary btn-block" data-callback="plz_ort_import_new">
                            <i class="fa fa-download"></i> {__('plz_ort_import_new')}
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="modalWait" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>{__('plz_ort_import_load')} <img src="{$adminURL}/templates/bootstrap/gfx/widgets/ajax-loader.gif"></h2>
            </div>
        </div>
    </div>
</div>
<div id="modalTempImport" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fal fa-exclamation-triangle"></i> {__('plz_ort_import')}</h2>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                {__('plz_ort_import_tmp_exists')}
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a href="#" class="btn btn-outline-primary" data-dismiss="modal"><i class="fa fa-exclamation"></i> {__('plz_ort_import_delete_no')}</a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <a href="#" class="btn btn-primary" data-callback="plz_ort_import_delete_temp" data-dismiss="modal"><i class="fas fa-trash-alt"></i> {__('plz_ort_import_delete_yes')}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modalHelp" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fa fa-question-circle"></i> {__('plz_ort_import')}</h2>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                {{__('plz_ort_import_help')}|sprintf:{$smarty.const.PLZIMPORT_URL}}
            </div>
            <div class="modal-footer text-right">
                <a href="#" class="btn btn-primary" data-dismiss="modal"><i class="fal fa-check text-success"></i> {__('ok')}</a>
            </div>
        </div>
    </div>
</div>
<script type="application/javascript">{literal}

    var running   = false,
        notify    = null,
        startTick = null;

    var shortGermanHumanizer = humanizeDuration.humanizer({
        round: true,
        delimiter: ' ',
        units: ['h', 'm', 's'],
        language: 'shortDE',
        languages: {
            shortDE: {
                h: function () {
                    return 'Std'
                },
                m: function () {
                    return 'Min'
                },
                s: function () {
                    return 'Sek'
                }
            }
        }
    });

    function showModalWait(onShow) {
        var $modalWait = $("#modalWait");
        if ((typeof onShow) === 'function') {
            $modalWait.on('shown.bs.modal', onShow);
        }
        $modalWait.modal({backdrop: false});

        return $modalWait;
    }

    function showImportNotify(title, message) {
        return createNotify({
            title: title,
            message: message
        }, {
            allow_dismiss: true,
            showProgressbar: true,
            delay: 0,
            onClose: function () {
                stopImport();
                updateIndex();
            }
        });
    }

    function stopImport() {
        if (running) {
            $('[data-callback]').attr('disabled', false);
            running = false;
        }
    }

    function updateIndex() {
        $('[data-callback]').attr('disabled', true);
        ioCall('plzimportActionUpdateIndex', [], function(result) {
            if (result) {
                window.location.reload();
            }
            $('[data-callback]').attr('disabled', false);
        }, {}, {}, true);
    }

    function refreshNotify() {
        if (running) {
            ioCall('plzimportActionCallStatus', [], function(result) {
                if (result && result.running) {
                    var offsetTick = new Date().getTime() - startTick,
                        perItem    = Math.floor(offsetTick / result.step),
                        eta        = Math.max(0, Math.ceil((100 - result.step) * perItem)),
                        readable   = shortGermanHumanizer(eta);

                    notify.update({
                        progress: result.step,
                        message: result.status + ' (' + readable + ' verbleiben)'
                    });

                    window.setTimeout(refreshNotify, 1500);
                } else {
                    window.setTimeout(function(){
                        notify.close();
                    }, 3000);
                }
            }, {}, {}, true);
        }
    }

    function startImport(ref, part) {
        $('[data-callback]').attr('disabled', true);
        part      = part || '';
        running   = true;
        startTick = new Date();
        notify    = showImportNotify('PLZ-Orte Import', 'Import wird gestartet...');

        var callback = function(result) {
            stopImport();
            updateIndex();
            notify.update({
                progress: 100,
                message: '&nbsp;',
                type: result ? result.type : 'danger',
                title: result ? result.message : 'Ups...'
            });
            window.setTimeout(function(){
                notify.close();
            }, 3000);
        };

        window.setTimeout(refreshNotify, 1500);
        ioCall('plzimportActionDoImport', [ref, part], callback, function(result) {
            ioCall('plzimportActionResetImport', ['danger', 'Fehler beim Import... Import abgebrochen!'], callback, {}, {}, true);
        }, {}, {}, true);
    }

    function startBackup(ref) {
        var $modalWait = showModalWait(function() {
            ioCall('plzimportActionRestoreBackup', [ref], function(result) {
                $modalWait.modal('hide');
                updateIndex();
            }, {}, {}, true);
        });
    }

    function checkRunning() {
        ioCall('plzimportActionCheckStatus', [], function(result) {
            if (result) {
                if (result.running) {
                    $('[data-callback]').attr('disabled', true);
                    running   = true;
                    startTick = new Date();
                    startTick.setTime(result.start);
                    notify = showImportNotify('PLZ-Orte Import', 'Import wird gestartet...');

                    refreshNotify();
                } else if (result.tmp > 0) {
                    plz_ort_import_exists();
                }
            }
        }, {}, {}, true);
    }

    function plz_ort_import_exists() {
        showBackdrop();
        var $modal = $('#modalTempImport');
        $modal.on('hide.bs.modal', function () {
            hideBackdrop();
        });
        $modal.modal({backdrop: false});
    }

    function plz_ort_import_delete_temp() {
        notify = showImportNotify('PLZ-Orte Import', 'Temporärer Import wird gelöscht...');
        ioCall('plzimportActionDelTempImport', [], function(result) {
            notify.update({
                progress: 100,
                message: '&nbsp;',
                type: result ? result.type : 'danger',
                title: result ? result.message : 'Ups...'
            });
            window.setTimeout(function(){
                notify.close();
            }, 3000);
        }, {}, {}, true);
    }

    function plz_ort_import_new($el) {
        showBackdrop();
        var $modal = $('#modalSelect');
        if ($modal.length === 0) {
            var $modalWait = showModalWait(function() {
                ioCall('plzimportActionLoadAvailableDownloads', [], function (result) {
                    $modal = $(result.dialogHTML);
                    $modal.on('hide.bs.modal', function () {
                        hideBackdrop();
                    });
                    $modalWait.one('hidden.bs.modal', function () {
                        $modal.modal({backdrop: false});
                    }).modal('hide');
                }, {}, {}, true);
            });
        } else {
            $modal.modal({backdrop: false});
        }
    }

    function plz_ort_import($el) {
        var ref = $el.data('ref');
        $('#modalSelect').modal('hide');
        startImport(ref);
    }

    function plz_ort_import_refresh($el) {
        var ref = $el.data('ref');
        startImport(ref, 'import');
    }

    function plz_ort_import_reset($el) {
        var ref = $el.data('ref');
        startBackup(ref);
    }

    $(function () {
        $('#content_wrapper > .content-header p.description').append(
                '<a href="#modalHelp" data-toggle="modal" data-backdrop="false"><i class="fa fa-question-circle"></i></a>'
        );
        $('#modalHelp').on('show.bs.modal', function(){
            showBackdrop();
        }).on('hide.bs.modal', function(){
            hideBackdrop();
        });

        $(document).on('click', '[data-callback]', function (e) {
            e.preventDefault();
            var $element = $(this);
            if ($element.attr('disabled') !== undefined) {
                return false;
            }
            var callback = $element.data('callback');
            if (!$(e.target).attr('disabled')) {
                window[callback]($element);
            }
        });

        checkRunning();
    });
</script>{/literal}
