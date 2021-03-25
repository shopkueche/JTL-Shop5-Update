class OPC extends Emitter
{
    constructor(env)
    {
        super();

        bindProtoOnHandlers(this);
        setJtlToken(env.jtlToken);
        installJqueryFixes();

        this.messages     = env.messages;
        this.error        = env.error;
        this.messages     = env.messages;
        this.io           = new IO();
        this.page         = new Page(this.io, env.shopUrl, env.pageKey);
        this.gui          = new GUI(this.io, this.page, env.messages);
        this.iframe       = new Iframe(this, this.io, this.gui, this.page, env.shopUrl, env.templateUrl);
        this.tutorial     = new Tutorial(this.iframe);
        this.pagetree     = new PageTree(this.page, this.iframe, this.gui);
        this.previewFrame = new PreviewFrame();
    }

    init()
    {
        this.io.init()
            .then(() => this.gui.init(this.iframe, this.previewFrame, this.tutorial, this.error))
            .then(() => {
                this.tutorial.init();
                this.pagetree.init();
                this.previewFrame.init();
                return this.page.lock(er => {
                    if(er === 1) {
                        this.gui.showError(this.messages.opcPageLocked);
                    } else if(er === 2) {
                        this.gui.showError(this.messages.dbUpdateNeeded);
                    }
                });
            })
            .then(() => this.page.loadDraft())
            .then(() => this.iframe.init(this.pagetree))
            .then(() => {
                this.gui.updateRevisionList();
                this.gui.hideLoader();
                this.pagetree.render();

                if(this.page.hasUnsavedContent()) {
                    this.gui.showRestoreUnsaved();
                    this.gui.unsavedRevision.show();
                } else {
                    this.gui.unsavedRevision.hide();
                }
            });
    }

    selectImageProp(propName)
    {
        this.gui.selectImageProp(propName);
    }

    selectVideoProp(propName)
    {
        this.gui.selectVideoProp(propName);
    }

    setConfigSaveCallback(callback)
    {
        this.gui.setConfigSaveCallback(callback);
    }

    setImageSelectCallback(callback)
    {
        this.gui.setImageSelectCallback(callback);
    }
}
