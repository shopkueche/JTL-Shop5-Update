<script>
    var adminPath = '{$PFAD_ADMIN}';
    {literal}

    function backup($element)
    {
        disableUpdateControl(true);

        var url = $element.attr('href'),
            download = !!$element.data('download');

        pushEvent('Starte Sicherungskopie');
        ioManagedCall(
            adminPath, 'dbupdaterBackup', [],
            function (result, error) {
                disableUpdateControl(false);

                var message = error
                    ? '{/literal}{__('errorSaveCopy')}{literal}'
                    : (download
                        ? '{/literal}{__('saveCopy')}{literal}' + '"<strong>' + result.file + '</strong>"' + '{/literal}{__('isDownloaded')}{literal}'
                        : '{/literal}{__('saveCopy')}{literal}' + '"<strong>' + result.file + '</strong>"' + '{/literal}{__('createSuccess')}{literal}');

                showNotify(error ? 'danger' : 'success', 'Sicherungskopie', message);
                pushEvent(message);

                if (!error && download) {
                    ioDownload('dbupdaterDownload', [result.file]);
                }
            }
        );
    }

    function doUpdate(callback)
    {
        ioManagedCall(
            adminPath, 'dbUpdateIO', [],
            function (result, error) {
                if (!error) {
                    callback(result);

                    if (result.availableUpdate) {
                        doUpdate(callback);
                    } else {
                        location.reload();
                    }
                } else {
                    callback(undefined, error);
                }
            }
        );
    }

    function update($element)
    {
        var url = $element.attr('href');

        disableUpdateControl(true);
        pushEvent('Starte Update');

        doUpdate(function(data, error) {
            var _once = function() {
                var message = error
                    ? '{/literal}{__('infoUpdatePause')}{literal}' + error.message
                    : '{/literal}{__('successUpdate')}{literal}'

                showNotify(error ? 'danger' : 'success', 'Update', message);
                disableUpdateControl(false);
            };

            if (error) {
                pushEvent('Fehler bei Update: ' + error.message);
                _once();
            }
            else {
                pushEvent('     Update auf ' + formatVersion(data.result) + ' erfolgreich');
                if (!data.availableUpdate) {
                    //pushEvent('Update beendet');
                    updateStatusTpl(null);
                    _once();
                }
            }
        });
    }

    function updateStatusTpl(plugin)
    {
        ioManagedCall(adminPath, 'dbupdaterStatusTpl', [plugin], function(result, error) {
            if (error) {
                pushEvent(error.message);
            } else {
                $('#update-status').html(result.tpl);
                init_bindings();
            }
        });

        // update notifications
        updateNotifyDrop();
    }

    function toggleDirection($element)
    {
        $element.parent()
            .children()
            .attr('disabled', false)
            .toggle();
    }

    /**
     * @deprecated since 4.06
     * @param $element
     */
    function migrate($element)
    {
        var url = $element.attr('href'),
            $ladda = Ladda.create($('#migrate-button')[0]);

        $ladda.start();

        ajaxManagedCall(url, {}, function(result, error) {
            var count = error
                ? 0 : (typeof result.data.migrations === 'object'
                    ? result.data.migrations.length : 0);
            var message = error
                ? error.message
                : '<strong>' + count + '</strong>' + '{/literal}{__('successMigrations')}{literal}';

            $ladda.stop();
            updateStatusTpl(null);
            showNotify(error ? 'danger' : 'success', 'Migration', message);
        });
    }

    function migration($element)
    {
        var id = $element.data('id'),
            url = $element.attr('href'),
            dir = $element.data('dir'),
            plugin = $element.data('plugin'),
            params = {dir: dir};

        $element.attr('disabled', true);

        if (id !== undefined) {
            params = $.extend({}, { id: id }, params);
        }
        if (plugin === undefined) {
            plugin = null;
        }

        ioManagedCall(adminPath, 'dbupdaterMigration', [id, null, dir, plugin], function(result, error) {
            $element
                .attr('disabled', false)
                .closest('tr')
                .find('.migration-created')
                .fadeOut();

            if (!error) {
                toggleDirection($element);
            }

            var message = error
                ? error.message
                : '{/literal}{__('successMigration')}{literal}';

            showNotify(error ? 'danger' : 'success', 'Migration', message);

            if (!error) {
                if (result.forceReload === true) {
                    location.reload();
                }
                updateStatusTpl(plugin);
                if (dir === 'up') {
                    pushEvent(sprintf('{/literal}{__('updateTosuccessfull')}{literal}', formatVersion(result.result)));
                }
            }

            if (dir === 'down') {
                $('#resultLog').show();
            }
        });
    }

    /**
     * @deprecated since 4.06
     * @param url
     * @param params
     * @param callback
     */
    function ajaxManagedCall(url, params, callback)
    {
        ajaxCall(url, params, function(result, xhr) {
            if (xhr && xhr.error && xhr.error.code === 401) {
                createNotify({
                    title: '{/literal}{__('sessionExpired')}{literal}',
                    message: '{/literal}{__('redirectToLogin')}{literal}',
                    icon: 'fa fa-lock'
                }, {
                    type: 'danger',
                    onClose: function() {
                        window.location.pathname = '/' + adminPath + 'index.php';
                    }
                });
            } else if (typeof callback === 'function') {
                callback(result, result.error);
            }
        });
    }

    function pushEvent(message)
    {
        $('#debug').append($('<div/>').html(message));
    }

    function formatVersion(version)
    {
        var v = parseInt(version);
        if (v >= 300 && v < 500) {
            return v / 100;
        }
        return version;
    }

    function disableUpdateControl(disable)
    {
        var $container = $('#btn-update-group'),
            $buttons = $('#btn-update-group a.btn'),
            $ladda = Ladda.create($('#backup-button')[0]),
            $resultLog = $('#resultLog');

        if (!!disable) {
            $ladda.start();
            $buttons.attr('disabled', true);
            $resultLog.show();
        } else {
            $ladda.stop();
            $buttons.attr('disabled', false);
        }
    }

    function init_bindings()
    {
        $('[data-callback]').on('click', function(e) {
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
    }

    $(function() {
        init_bindings();
    });

    {/literal}
</script>
