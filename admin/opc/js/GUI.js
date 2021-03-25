class GUI
{
    constructor(io, page, messages)
    {
        bindProtoOnHandlers(this);

        this.io            = io;
        this.page          = page;
        this.messages      = messages;
        this.imageSelectCB = noop;
        this.iconPickerCB  = noop;
        this.inPreviewMode = false;
        this.loaderShown   = false;
    }

    init(iframe, previewFrame, tutorial, error)
    {
        this.iframe       = iframe;
        this.previewFrame = previewFrame;
        this.tutorial     = tutorial;

        installGuiElements(this, [
            'opcSidebar',
            'opcHeader',
            'btnPagetree',
            'iframePanel',
            'previewPanel',
            'loaderModal',
            'errorModal',
            'errorAlert',
            'errorTitle',
            'messageboxModal',
            'messageboxAlert',
            'configModal',
            'configModalTitle',
            'configPortletName',
            'configModalBody',
            'configForm',
            'stdConfigButtons',
            'missingConfigButtons',
            'blueprintModal',
            'blueprintName',
            'blueprintDeleteModal',
            'blueprintDeleteId',
            'publishModal',
            'draftName',
            'checkPublishNot',
            'checkPublishNow',
            'checkPublishSchedule',
            'checkPublishInfinite',
            'publishFrom',
            'publishTo',
            'revisionList',
            'revisionBtnBlueprint',
            'unsavedRevision',
            'blueprintList',
            'blueprintBtnBlueprint',
            'portletButton',
            'portletGroupBtn',
            'restoreUnsavedModal',
            'unsavedState',
            'iconpicker',
            'disableVeil',
        ]);

        this.missingConfigButtons.hide();

        if(error) {
            if(typeof error === 'string' && error.length > 0) {
                return this.showError(error);
            } else if(typeof error === 'object' && error.desc.length > 0) {
                return this.showError(error.desc, error.heading);
            }
        } else {
            this.showLoader();
            this.initDateTimePicker(this.publishFrom);
            this.initDateTimePicker(this.publishTo);

            this.publishFrom.on("change.datetimepicker", e => {
                this.publishTo.datetimepicker('minDate', e.date);
            });

            this.publishTo.on("change.datetimepicker", e => {
                this.publishFrom.datetimepicker('maxDate', e.date);
            });

            this.updateBlueprintList();
            this.updateRevisionList();

            this.iconpicker.iconpicker().on('iconpickerSelected', e => {
                this.iconPickerCB(e.iconpickerValue);
            });

            this.iconpicker.find('.popover-title').prepend('<i class=""></i>');

            this.configModal.on('hidden.bs.modal', e => {
                $('#opc').append(this.iconpicker);
            })
        }
    }

    initDateTimePicker(elm)
    {
        elm.datetimepicker({
            locale: 'de',
            format: localDateFormat,
            useCurrent: false,
            icons: {
                time: 'far fa-clock',
                date: 'far fa-calendar',
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                today: 'far fa-calendar-check',
                clear: 'fas fa-trash',
                close: 'fas fa-times',
            },
        });
    }

    showLoader()
    {
        this.loaderModal.one('shown.bs.modal', () => {
            this.loaderShown = true;
        });

        this.loaderModal.modal('show');
    }

    hideLoader()
    {
        this.loaderModal.one('hidden.bs.modal', () => {
            this.loaderShown = false;
        });

        if(this.loaderShown) {
            this.loaderModal.modal('hide');
        } else {
            this.loaderModal.one('shown.bs.modal', () => {
                this.loaderModal.modal('hide');
            });
        }
    }

    showRestoreUnsaved()
    {
        this.restoreUnsavedModal.modal('show');
    }

    showError(msg, heading)
    {
        if(heading) {
            this.errorTitle.html(heading);
        }

        this.loaderModal.modal('hide');
        this.errorAlert.html(msg);
        this.errorModal.modal('show');
        return Promise.reject(msg);
    }

    showMessageBox(msg, title)
    {
        this.messageboxAlert.html(msg);
        this.messageboxModal.find('.modal-title').html(title);
        this.messageboxModal.modal('show');
    }

    updateBlueprintList()
    {
        this.io.getBlueprints().then(blueprints => {
            this.blueprintList.empty();

            blueprints.forEach(blueprint => {
                let newBtn = this.blueprintBtnBlueprint.clone()
                    .attr('id', null)
                    .attr('data-blueprint-id', blueprint.id)
                    .show()
                    .appendTo(this.blueprintList);

                newBtn.find('.blueprintExport').attr('data-blueprint-id', blueprint.id);
                newBtn.find('.blueprintDelete').attr('data-blueprint-id', blueprint.id);
                newBtn.find('.blueprintTitle').text(blueprint.name);
            });

            this.updateDynamicGui();
        });
    }

    updateRevisionList()
    {
        this.page.getRevisionList().then(revisions => {
            this.revisionList.empty();

            revisions.forEach(rev => {
                this.revisionBtnBlueprint.clone()
                    .attr('id', '').css('display', '')
                    .attr('data-revision-id', rev.id)
                    .html(
                        '<div>' + rev.content.cName + '</div>' +
                        '<div>' + moment(rev.content.dLastModified, internalDateFormat).format(localDateFormat) + '</div>'
                    )
                    .appendTo(this.revisionList);
            });

            $('#currentLastModified').text(
                moment(this.page.lastModified, internalDateFormat).format(localDateFormat)
            );

            $('#currentDraftName').text(this.page.name);

            this.updateDynamicGui();
        });
    }

    updatePagetreeBtn()
    {
        if (this.page.offscreenAreas.length) {
            this.btnPagetree.addClass('has-unmapped');
        } else {
            this.btnPagetree.removeClass('has-unmapped');
        }
    }

    updateDynamicGui()
    {
        installGuiElements(this, [
            'blueprintButton',
            'blueprintExport',
            'blueprintDelete',
            'revisionBtn',
        ]);
    }

    importDraft()
    {
        this.page.loadFromImport()
            .catch(er => this.showError('Could not import OPC page JSON: ' + er.error.message))
            .then(this.iframe.onPageLoad)
            .then(() => {
                let unmappedCount = this.page.offscreenAreas.length;

                if (unmappedCount === 0) {
                    this.showMessageBox(
                        this.messages.opcImportSuccess,
                        this.messages.opcImportSuccessTitle,
                    );
                } else {
                    if (unmappedCount === 1) {
                        this.showMessageBox(
                            this.messages.opcImportSuccess + '<br><br>' + this.messages.opcImportUnmappedS,
                            this.messages.opcImportSuccessTitle,
                        );
                    } else {
                        this.showMessageBox(
                            this.messages.opcImportSuccess + '<br><br>' +
                            this.messages.opcImportUnmappedP.replace('%s', unmappedCount),
                            this.messages.opcImportSuccessTitle,
                        );
                    }

                    $('[href="#pagetree"]').click();
                    this.updatePagetreeBtn();
                    this.setUnsaved(true, true);
                }
            });
    }

    exportDraft()
    {
        this.page.exportAsDownload();
    }

    startHelp(e)
    {
        this.tutorial.start();
    }

    onBtnPreview()
    {
        if (this.inPreviewMode) {
            this.iframePanel.show();
            this.previewFrame.previewPanel.hide();
            this.inPreviewMode = false;
            this.disableVeil.hide();
        } else {
            this.iframePanel.hide();
            this.previewFrame.showPreview(this.page.fullUrl, JSON.stringify(this.page.toJSON()));
            this.inPreviewMode = true;
            this.disableVeil.show();
        }
    }

    savePage()
    {
        this.showLoader();
        this.page.save()
            .catch(error => this.showError('Page could not be saved: ' + error.error.message))
            .then(() => {
                this.hideLoader();
                this.updateRevisionList();
                this.setUnsaved(false, true);
            });
    }

    setUnsaved(enable, record)
    {
        record = record || false;

        if(enable) {
            this.unsavedState.show();

            if(record) {
                this.page.savePageToWebStorage();
                this.unsavedRevision.show();
            }
        } else {
            this.unsavedState.hide();

            if(record) {
                this.page.clearPageWebStorage();
                this.unsavedRevision.hide();
            }
        }
    }

    isPageUnsaved()
    {
        return this.unsavedState.css('display') !== 'none';
    }

    closeEditor(e)
    {
        this.page.unlock().then(() => {
            window.location = this.page.fullUrl;
        });
    }

    onPortletGroupBtn(e)
    {
        $(e.target)
            .find('i.fa')
            .toggleClass('fa-plus-circle fa-minus-circle');
    }

    onPortletButtonDragStart(e)
    {
        initDragStart(e);

        let portletBtn = $(e.target).closest('.portletButton');

        this.iframe.dragNewPortlet(
            portletBtn.data('portlet-class'),
            portletBtn.data('portlet-group'),
        );
    }

    onPortletButtonDragEnd(e)
    {
        this.iframe.dragNewPortlet();
        this.iframe.cleanUpDrag();
    }

    onBlueprintButtonDragStart(e)
    {
        initDragStart(e);

        let blueprintBtn = $(e.target).closest('.blueprintButton');

        this.iframe.dragNewBlueprint(blueprintBtn.data('blueprint-id'));
    }

    onBlueprintButtonDragEnd(e)
    {
        this.iframe.dragNewBlueprint();
        this.iframe.cleanUpDrag();
    }

    onRevisionBtn(e)
    {
        let elm   = $(e.target).closest('a');
        let revId = elm.data('revision-id');

        this.showLoader();

        this.page.loadRev(revId)
            .catch(er => this.showError('Error while loading draft preview: ' + er.error.message))
            .then(this.iframe.onPageLoad)
            .then(() => {
                this.iframe.loadMissingPortletPreviewStyles();
                this.updatePagetreeBtn();
            });

        this.setUnsaved(revId !== 0);
    }

    openConfigurator(portlet)
    {
        let portletData = portlet.data('portlet');

        this.setImageSelectCallback(noop);

        this.curPortlet = portlet;

        this.io.getConfigPanelHtml(
            portletData.class,
            portletData.missingClass,
            portletData.properties
        ).then(html => {
            if (portletData.class === 'MissingPortlet') {
                this.stdConfigButtons.hide();
                this.missingConfigButtons.show();
            } else {
                this.stdConfigButtons.show();
                this.missingConfigButtons.hide();
            }

            this.configModalBody.html(html);
            this.configPortletName[0].textContent = portletData.title;
            this.configModal.modal('show');
        });

    }

    saveConfig()
    {
        event.preventDefault();

        opc.emit('save-config');

        let portletData  = this.page.portletToJSON(this.curPortlet);
        let configObject = $(event.target).serializeControls();

        for(let propname in configObject) {
            if(configObject.hasOwnProperty(propname)) {
                let propval   = configObject[propname];
                let propInput = $('#config-' + propname);

                if (propInput.length > 0) {
                    let propType = propInput.data('prop-type');

                    if (propType === 'json') {
                        propval = JSON.parse(propval);
                    } else if (propType === 'datetime') {
                        propval = this.page.encodeDate(propval);
                    } else if (propInput[0].type === 'checkbox') {
                        propval = propval === '1';
                    } else if (propInput[0].type === 'number') {
                        propval = parseInt(propval);
                    }
                }

                configObject[propname] = propval;
            }
        }

        portletData.properties = configObject;

        this.io.getPortletPreviewHtml(portletData)
            .catch(er => {
                this.configModal.modal('hide');
                return this.showError('Error while saving Portlet configuration: ' + er.error.message);
            })
            .then(preview => {
                this.iframe.replaceSelectedPortletHtml(preview);
                this.configModal.modal('hide');
                this.page.updateFlipcards();
                this.iframe.disableLinks();
            });
    }

    createBlueprint()
    {
        event.preventDefault();

        if(this.selectedElm !== null) {
            let blueprintName = this.blueprintName.val();
            let blueprintData = this.page.portletToJSON(this.iframe.selectedElm);

            this.io.saveBlueprint(blueprintName, blueprintData).then(() => {
                this.updateBlueprintList();
            });

            this.blueprintModal.modal('hide');
        }
    }

    onBlueprintDelete(e)
    {
        let elm   = $(e.target).closest('.blueprintDelete');
        let title = elm.closest('.blueprintButton').find('.blueprintTitle').text();

        $('#blueprintDeleteTitle').text(title);
        this.blueprintDeleteId.val(elm.data('blueprint-id'));
        this.blueprintDeleteModal.modal('show');
    }

    onBlueprintExport(e)
    {
        let elm         = $(e.target).closest('.blueprintExport');
        let blueprintId = elm.data('blueprint-id');

        this.io.getBlueprint(blueprintId).then(blueprint => {
            download(JSON.stringify(blueprint), blueprint.name + '.json', 'application/json');
        });
    }

    importBlueprint()
    {
        $('<input type="file" accept=".json">')
            .on(
                'change',
                e => {
                    this.importReader = new FileReader();
                    this.importReader.onload = () => {
                        let blueprint = JSON.parse(this.importReader.result);
                        this.io.saveBlueprint(blueprint.name, blueprint.instance)
                            .then(() => this.updateBlueprintList());
                    };
                    this.importReader.readAsText(e.target.files[0]);
                }
            )
            .click();
    }

    deleteBlueprint()
    {
        let blueprintId = this.blueprintDeleteId.val();

        this.io.deleteBlueprint(blueprintId).then(() => this.updateBlueprintList());
        this.blueprintDeleteModal.modal('hide');

        event.preventDefault();
    }

    publishDraft()
    {
        if(typeof this.page.publishFrom === 'string' && this.page.publishFrom.length > 0) {
            this.setPublishSchedule();
            this.publishFrom.val(this.page.publishFrom);

            if(typeof this.page.publishTo === 'string' && this.page.publishTo.length > 0) {
                this.unsetInfiniteSchedule();
                this.publishTo.val(this.page.publishTo);
            } else {
                this.setInfiniteSchedule();
            }
        } else {
            this.setPublishNow();
        }

        this.draftName.val(this.page.name);
        this.publishModal.modal('show');
    }

    onChangePublishStrategy()
    {
        if (this.checkPublishNot.prop('checked')) {
            this.setUnpublished();
        } else if (this.checkPublishNow.prop('checked')) {
            this.setPublishNow();
        } else {
            this.setPublishSchedule();
        }
    }

    onChangePublishInfinite()
    {
        if(this.checkPublishInfinite.prop('checked')) {
            this.setInfiniteSchedule();
        } else {
            this.unsetInfiniteSchedule();
        }
    }

    setUnpublished()
    {
        this.checkPublishNot.prop('checked', true);
        this.publishFrom.prop('disabled', true);
        this.publishFrom.val('Unveröffentlicht');
        this.publishTo.prop('disabled', true);
        this.publishTo.val('Auf unbestimmte Zeit');
        this.checkPublishInfinite.prop('checked', true);
        this.checkPublishInfinite.prop('disabled', true);
    }

    setPublishNow()
    {
        this.checkPublishNow.prop('checked', true);
        this.publishFrom.prop('disabled', true);
        this.publishFrom.val('Jetzt');
        this.publishTo.prop('disabled', true);
        this.publishTo.val('Auf unbestimmte Zeit');
        this.checkPublishInfinite.prop('checked', true);
        this.checkPublishInfinite.prop('disabled', true);
    }

    setPublishSchedule()
    {
        this.checkPublishSchedule.prop('checked', true);
        this.publishFrom.prop('disabled', false);
        this.publishFrom.val(moment().format(localDateFormat));
        this.checkPublishInfinite.prop('disabled', false);
    }

    setInfiniteSchedule()
    {
        this.checkPublishInfinite.prop('checked', true);
        this.publishTo.prop('disabled', true);
        this.publishTo.val('Auf unbestimmte Zeit');
        this.publishFrom.datetimepicker('maxDate', false);
    }

    unsetInfiniteSchedule()
    {
        this.checkPublishInfinite.prop('checked', false);
        this.publishTo.prop('disabled', false);
        this.publishTo.val(moment(this.publishFrom.val(), localDateFormat).add(1, 'M').format(localDateFormat));
    }

    publish()
    {
        event.preventDefault();

        this.page.name = this.draftName.val();
        $('#footerDraftName span').text(this.page.name);

        if (this.checkPublishNot.prop('checked')) {
            this.page.publishFrom = null;
        } else if (this.checkPublishNow.prop('checked')) {
            this.page.publishFrom = moment().format(localDateFormat);
        } else {
            let datetime = moment(this.publishFrom.val(), localDateFormat);

            if (datetime.isValid() === false) {
                throw this.showError('Invalid From Date');
            }

            this.page.publishFrom = this.publishFrom.val();
        }

        if (this.checkPublishInfinite.prop('checked')) {
            this.page.publishTo = null;
        } else {
            let datetime = moment(this.publishTo.val(), localDateFormat);

            if (datetime.isValid() === false) {
                throw this.showError('Invalid To Date');
            }

            this.page.publishTo = this.publishTo.val();
        }

        this.page.publicate()
            .catch(er => this.showError(er.error.message))
            .then(() => this.io.getDraftStatusHtml(this.page.key));

        if (this.isPageUnsaved()) {
            this.savePage();
        }

        this.publishModal.modal('hide');
    }

    selectImageProp(propName)
    {
        this.openElFinder((file, mediafilesBaseUrlPath) => {
            let url = file.url.slice(mediafilesBaseUrlPath.length);
            this.imageSelectCB(url, propName, file.url);
            this.configForm.find('[name="' + propName + '"]').val(url);
            this.configForm.find('#preview-img-' + propName).attr('src', file.url);
        }, 'image');
    }

    selectVideoProp(propName)
    {
         this.openElFinder(file => {
             this.configForm.find('[name="' + propName + '"]').val(file.url);
             this.configForm.find('#preview-vid-' + propName).attr('src', file.url);
             this.configForm.find('#cont-preview-vid-' + propName)[0].load();
         }, 'video');
    }

    openElFinder (callback, type)
    {
        openElFinder(callback, type);
    }

    restoreUnsaved()
    {
        event.preventDefault();

        this.unsavedRevision.click();
        this.restoreUnsavedModal.modal('hide');
    }

    setImageSelectCallback(callback)
    {
        this.imageSelectCB = callback;
    }

    setIconPickerCallback(callback)
    {
        this.iconPickerCB = callback;
    }

    setDisplayFrameWidth(btn, value)
    {
        this.iframe.iframe.width(value);
        this.previewFrame.previewFrame.width(value);
        $('#displayWidths .active').removeClass('active');
        $(btn).addClass('active');
    }

    setDisplayWidthXS()
    {
        this.setDisplayFrameWidth(event.currentTarget, '375px');
    }

    setDisplayWidthSM()
    {
        this.setDisplayFrameWidth(event.currentTarget, '577px');
    }

    setDisplayWidthMD()
    {
        this.setDisplayFrameWidth(event.currentTarget, '769px');
    }

    setDisplayWidthLG()
    {
        this.setDisplayFrameWidth(event.currentTarget, '993px');
    }

    setDisplayWidthXL()
    {
        this.setDisplayFrameWidth(event.currentTarget, '100%');
    }

    onBeginEditDraftName()
    {
        $('#footerDraftName').hide();
        $('#footerDraftNameInput').val(this.page.name).show();
    }

    onFinishEditDraftName()
    {
        let draftNameSpan = $('#footerDraftName');
        let draftNameInput = $('#footerDraftNameInput');
        let draftName = draftNameInput.val();

        if(draftName === '' || this.escapedDraftNameInput === true) {
            this.escapedDraftNameInput = false;
            draftNameSpan.show();
        } else {
            this.io.changeDraftName(this.page.key, draftName).then(() => {
                this.page.name = draftName;
                $('#footerDraftName span').text(draftName);
            });
        }

        draftNameInput.hide();
        draftNameSpan.show();
    }

    onDraftNameInputKeydown()
    {
        if (event.key === 'Enter') {
            $('#footerDraftNameInput').blur();
        } else if(event.key === 'Escape') {
            this.escapedDraftNameInput = true;
            $('#footerDraftNameInput').blur();
        }
    }

    noRestoreUnsaved()
    {
        this.setUnsaved(false, true);
    }
}
