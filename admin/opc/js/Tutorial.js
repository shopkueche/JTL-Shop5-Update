class Tutorial
{
    constructor(iframe)
    {
        bindProtoOnHandlers(this);

        this.iframe        = iframe;
        this.tourId        = null;
        this.stepId        = null;
        this.handlers      = [];
        this.wasUserAction = false;
    }

    init()
    {
        installGuiElements(this, [
            'opcSidebar',
            'iframePanel',
            'previewPanel',
            'portlets',
            'tourModal',
            'tutorials',
            'tutBackdrop',
            'tutBackdrop2',
            'tutBackdrop3',
            'tutBox',
            'tutboxTitle',
            'tutboxContent',
            'tutboxNext',
            'tutboxPrev',
            'tutboxNextLabel',
            'tutboxDoneLabel',
        ]);
    }

    start()
    {
        this.tourModal.modal('show');
    }

    startTour(tourId)
    {
        this.tourId = parseInt(tourId);
        this.tourModal.modal('hide');
        this.tutorials.addClass('active');
        this.startStep(0);
    }

    reset()
    {
        this.tutBox.removeClass('centered-c');
        this.tutBox.removeClass('centered-v');
        this.tutBox.removeClass('centered-h');
        this.tutBox.css('left', '');
        this.tutBox.css('top', '');
        this.tutBox.css('right', '');
        this.tutBox.css('bottom', '');
        this.tutboxNext.prop('disabled', false);
        this.tutBackdrop.css('width', '');
        this.tutBackdrop2.remove();
        this.tutBackdrop2.removeClass('active');
        this.tutBackdrop2.css('width', '');
        this.tutBackdrop2.css('height', '');
        this.tutBackdrop3.removeClass('active');
        this.tutBackdrop3.appendTo(this.tutorials);
        this.iframe.jq('.hightlighted-element').removeClass('hightlighted-element');
        $('.hightlighted-element').removeClass('hightlighted-element');
        $('.hightlighted-modal').removeClass('hightlighted-modal');
    }

    startStep(stepId)
    {
        let title   = opc.messages["tutStepTitle_" + this.tourId + "_" + stepId];
        let content = opc.messages["tutStepText_" + this.tourId + "_" + stepId];

        this.stepId = stepId;
        this.reset();
        this.tutboxTitle.html(title);
        this.tutboxContent.html(content);
        this.tutboxPrev.prop('disabled', stepId === 0);

        if(this.wasUserAction) {
            this.wasUserAction = false;
            this.tutboxPrev.prop('disabled', true);
        }

        if(opc.messages["tutStepTitle_" + this.tourId + "_" + (stepId + 1)]) {
            this.tutboxNextLabel.show();
            this.tutboxDoneLabel.hide();
        } else {
            this.tutboxNextLabel.hide();
            this.tutboxDoneLabel.show();
        }

        switch(this.tourId) {
            case 0:
                switch (stepId) {
                    case 0: {
                        this.makeTutbox({cls: 'c'});
                        break;}
                    case 1: {
                        this.makeTutbox({cls: 'v', left: 32});
                        this.highlightElms(this.opcSidebar);
                        break;}
                    case 2: {
                        this.makeTutbox({cls: 'v', left: this.opcSidebar.width() + 64});
                        this.highlightElms(this.iframePanel, this.previewPanel);
                        break;}
                    case 3: {
                        this.makeTutbox({cls: 'v', left: this.opcSidebar.width() - 32});
                        this.highlightElms(this.portlets);
                        break;}
                    case 4: {
                        this.makeTutbox({cls: 'v', left: this.opcSidebar.width() + 64});
                        this.makeBackdrop('iframe');
                        this.highlightElms(this.iframe.dropTargets());
                        break;}
                    case 5: {
                        this.makeTutbox({cls: 'v', left: this.opcSidebar.width() + 64});
                        this.makeBackdrop('iframe');
                        this.highlightElms($('[data-portlet-class="Heading"]'), this.iframe.dropTargets());
                        this.bindResetEvent(opc.page.rootAreas, 'drop');
                        this.bindNextEvent($('#configModal'), 'shown.bs.modal');
                        break;}
                    case 6: {
                        this.makeTutbox({cls: 'c'});
                        this.tutBackdrop3.addClass('active');
                        break;}
                    case 7: {
                        let modal = $('#configModal');
                        this.makeTutbox({cls: 'c'});
                        this.makeBackdrop('modal', modal);
                        this.highlightElms($('#config-text'), $('#configSave'));
                        this.bindResetEvent(modal, 'hide.bs.modal');
                        this.bindNextEvent(modal, 'hidden.bs.modal');
                        break;}
                    case 8: {
                        this.makeTutbox({cls: 'c', left: this.opcSidebar.width() + 64});
                        this.makeBackdrop('iframe');
                        this.tutBackdrop3.addClass('active');
                        this.highlightElms(this.iframe.portletToolbar);
                        break;}
                    case 9:{
                        let modal = $('#publishModal');
                        let btn   = $('#btnPublishDraft');
                        this.makeTutbox({left: this.opcSidebar.width() + 64, top: btn.offset().top - 150});
                        this.highlightElms(btn);
                        this.bindResetEvent(modal, 'show.bs.modal');
                        this.bindNextEvent(modal, 'shown.bs.modal');
                        break;}
                    case 10:{
                        let modal  = $('#publishModal');
                        let dialog = modal.find('.modal-dialog');
                        this.makeTutbox({cls: 'h', top: this.elmBottom(dialog, 32)});
                        this.tutBackdrop3.addClass('active');
                        break;}
                    case 11:{
                        let modal  = $('#publishModal');
                        let dialog = modal.find('.modal-dialog');
                        this.makeTutbox({cls: 'h', top: this.elmBottom(dialog, 32)});
                        this.makeBackdrop('modal', modal);
                        this.highlightElms($('#btnCancelPublish'));
                        this.bindResetEvent(modal, 'hide.bs.modal');
                        this.bindNextEvent(modal, 'hidden.bs.modal');
                        break;}
                    default:
                        this.makeTutbox({cls: 'c'});
                        break;
                }
                break;
            case 1:
                switch (stepId) {
                    case 0: {
                        this.makeTutbox({cls: 'c'});
                        break;}
                    case 1: {
                        let buttonPortlet = $('[data-portlet-class="Button"]');
                        this.makeTutbox({left: 32, top: this.elmBottom(buttonPortlet, 32)});
                        this.makeBackdrop('iframe');
                        this.highlightElms(buttonPortlet, this.iframe.dropTargets());
                        this.bindResetEvent(opc.page.rootAreas, 'drop');
                        this.bindNextEvent($('#configModal'), 'shown.bs.modal');
                        break;}
                    case 2: {
                        let modal        = $('#configModal');
                        let modalContent = modal.find('.modal-content');
                        let dialog       = modal.find('.modal-dialog');
                        let animTab      = $('[href="#conftab3"]');
                        this.tutBackdrop3.addClass('active');
                        this.tutBackdrop3.appendTo(modalContent);
                        this.highlightElms(animTab.closest('.nav-item'));
                        this.makeTutbox({cls: 'h', top: this.elmBottom(dialog, 32)});
                        this.bindNextEvent(animTab, 'shown.bs.tab');
                        break;}
                    case 3: {
                        let modal  = $('#configModal');
                        let formgr = $('#config-animation-style').closest('.form-group');
                        this.makeTutbox({left: formgr.offset().left, top: this.elmBottom(formgr, 32)});
                        this.makeBackdrop('modal', modal);
                        this.highlightElms(formgr, $('#configSave'));
                        this.bindResetEvent(modal, 'hide.bs.modal');
                        this.bindNextEvent(modal, 'hidden.bs.modal');
                        break;}
                    case 4: {
                        this.makeTutbox({cls:'c'});
                        this.makeBackdrop('iframe');
                        this.highlightElms(opc.iframe.selectedElm);
                        break;}
                    case 5: {
                        let modal    = $('#configModal');
                        this.makeTutbox({cls:'c'});
                        this.makeBackdrop('iframe');
                        this.highlightElms(opc.iframe.selectedElm, this.iframe.portletToolbar);
                        this.bindResetEvent(modal, 'show.bs.modal');
                        this.bindNextEvent(modal, 'shown.bs.modal');
                        break;}
                    case 6: {
                        let styleTab = $('[href="#conftab2"]');
                        let animTab  = $('[href="#conftab3"]');
                        let modal    = $('#configModal');
                        styleTab.click();
                        styleTab.one('shown.bs.tab', () => {
                            let marginInp = $('#margin-bottom-input');
                            this.makeBackdrop('modal', modal);
                            this.makeTutbox({cls:'h', top: this.elmBottom(marginInp, 32)});
                            this.highlightElms(marginInp, animTab.closest('.nav-item'),
                                styleTab.closest('.nav-item'));
                            this.bindNextEvent(animTab, 'shown.bs.tab');
                        });
                        break;}
                    case 7: {
                        let modal   = $('#configModal');
                        let formgr  = $('#config-animation-style').closest('.form-group');
                        let formgr2 = $('#config-wow-offset').closest('.form-group');
                        this.makeTutbox({cls:'c'});
                        this.makeTutbox({left: formgr2.offset().left, top: this.elmBottom(formgr2, 32)});
                        this.makeBackdrop('modal', modal);
                        this.highlightElms(formgr, formgr2, $('#configSave'));
                        this.bindResetEvent(modal, 'hide.bs.modal');
                        this.bindNextEvent(modal, 'hidden.bs.modal');
                        break;}
                    case 8: case 9: case 10: {
                        let tb = this.iframe.portletToolbar;
                        this.makeTutbox({left: 32, top: 128});
                        this.makeBackdrop('iframe');
                        this.highlightElms(opc.iframe.selectedElm, tb);
                        this.bindNextEvent(this.iframe.jq('#btnClone'), 'click');
                        break;}
                    case 11: {
                        let toggle = $('#previewToolbar').find('.toggle-switch');
                        this.makeTutbox({left: 32, top: toggle.offset().top - 200});
                        this.highlightElms(toggle);
                        this.bindNextEvent(toggle, 'click');
                        break;}
                    case 12: {
                        let toggle = $('#previewToolbar').find('.toggle-switch');
                        this.makeTutbox({left: 32, top: toggle.offset().top - 400});
                        this.makeBackdrop('none');
                        this.highlightElms(toggle, $('#previewPanel'));
                        break;}
                    default:
                        this.makeTutbox({cls:'c'});
                        break;
                }
                break;
            case 2:
                switch (stepId) {
                    case 0: {
                        this.makeBackdrop('none');
                        this.makeTutbox({left: this.opcSidebar.width() + 16, bottom: 16});
                        break;}
                    case 1: {
                        this.makeBackdrop('none');
                        this.makeTutbox({left: this.opcSidebar.width() + 16, bottom: 16});

                        let handler = e => {
                            if(opc.iframe.findSelectableParent(opc.iframe.jq(e.target))) {
                                setTimeout(() => this.tutboxNext.click());
                            } else {
                                this.bindEvent(opc.page.rootAreas, 'click', handler);
                            }
                        };

                        this.bindEvent(opc.page.rootAreas, 'click', handler);
                        this.tutboxNext.prop('disabled', true);
                        break;}
                    case 2: {
                        let modal = $('#blueprintModal');
                        let tb    = this.iframe.portletToolbar;
                        this.makeBackdrop('iframe');
                        this.makeTutbox({left: tb.offset().left + this.opcSidebar.width(),
                            top: tb.offset().top + 64});
                        this.highlightElms(this.iframe.portletToolbar);
                        this.bindResetEvent(modal, 'show.bs.modal');
                        this.bindNextEvent(modal, 'shown.bs.modal');
                        break;}
                    case 3: {
                        let modal        = $('#blueprintModal');
                        let dialog       = modal.find('.modal-dialog');
                        this.makeBackdrop('modal', modal);
                        this.makeTutbox({cls: 'h', top: this.elmBottom(dialog, 32)});
                        this.highlightElms($('#blueprintName'), $('#btnBlueprintSave'));
                        this.bindResetEvent(modal, 'hide.bs.modal');
                        this.bindNextEvent(modal, 'hidden.bs.modal');
                        break;}
                    case 4: {
                        let tab = $('[href="#blueprints"]');
                        this.makeTutbox({left: tab.offset().left, top: this.elmBottom(tab, 32)});
                        this.highlightElms(tab);
                        this.bindNextEvent(tab, 'shown.bs.tab');
                        break;}
                    case 5: {
                        this.makeTutbox({left: this.opcSidebar.width() + 16, bottom: 16});
                        this.highlightElms(this.iframe.dropTargets(), $('#blueprints'));
                        this.makeBackdrop('iframe');
                        this.bindNextEvent(opc.page.rootAreas, 'drop');
                        break;}
                    case 6: {
                        this.makeTutbox({left: this.opcSidebar.width() + 16, bottom: 16});
                        this.makeBackdrop('none');
                        break;}
                    default:
                        this.makeTutbox({cls:'c'});
                        break;
                }
                break;
        }
    }

    elmRight(elm, extraOffset)
    {
        return elm.offset().left + elm.width() + extraOffset;
    }

    elmBottom(elm, extraOffset)
    {
        return elm.offset().top + elm.height() + extraOffset;
    }

    goNextStep()
    {
        let nextStep = this.stepId + 1;

        if(opc.messages["tutStepTitle_" + this.tourId + "_" + nextStep]) {
            this.startStep(nextStep);
        } else {
            this.stopTutorial();
        }
    }

    goPrevStep()
    {
        let nextStep = this.stepId - 1;

        if(opc.messages["tutStepTitle_" + this.tourId + "_" + nextStep]) {
            this.unbindEvents();
            this.startStep(nextStep);
        } else {
            this.stopTutorial();
        }
    }

    bindEvent(elm, event, handler)
    {
        elm.one(event + '.tutorial', handler);
        this.handlers.push(elm);
    }

    bindNextEvent(elm, event)
    {
        this.bindEvent(elm, event, () => this.tutboxNext.click());
        this.tutboxNext.prop('disabled', true);
        this.wasUserAction = true;
    }

    bindResetEvent(elm, event)
    {
        this.bindEvent(elm, event, () => this.reset());
    }

    unbindEvents()
    {
        this.handlers.forEach(h => h.off('.tutorial'));
        this.handlers = [];
    }

    stopTutorial()
    {
        this.reset();
        this.tutorials.removeClass('active');
        this.unbindEvents();
    }

    makeTutbox({cls, left, top, right, bottom, disable})
    {
        if(cls)     this.tutBox.addClass('centered-' + cls);
        if(left)    this.tutBox.offset({left: left});
        if(top)     this.tutBox.offset({top: top});
        if(right)   this.tutBox.css('right', right + 'px');
        if(bottom)  this.tutBox.css('bottom', bottom + 'px');

        if(disable) {
            this.tutboxNext.prop('disabled', true);
            this.wasUserAction = true;
        }
    }

    makeBackdrop(type, modal)
    {
        switch(type) {
            case 'none':
                this.tutBackdrop.width(0);
                break;
            case 'iframe':
                this.tutBackdrop.width(this.opcSidebar.width());
                this.tutBackdrop2.appendTo(this.iframe.body);
                this.tutBackdrop2.addClass('active');
                break;
            case 'modal':
                let modalContent = modal.find('.modal-content');
                this.tutBackdrop2.appendTo(modalContent);
                this.tutBackdrop2.addClass('active');
                break;
        }
    }

    highlightElms(...elms)
    {
        elms.forEach(elm => elm.addClass('hightlighted-element'));
    }
}
