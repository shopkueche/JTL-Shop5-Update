class Page
{
    constructor(io, shopUrl, key)
    {
        bindProtoOnHandlers(this);

        this.io             = io;
        this.shopUrl        = shopUrl;
        this.key            = key;
        this.lockTimeout    = null;
        this.offscreenAreas = {};
    }

    lock(errorcb)
    {
        return this.io.lockDraft(this.key).then(state => {
            if (state === 0) {
                this.lockTimeout = setTimeout(() => {
                    this.lock(errorcb);
                }, 1000 * 60);

                return Promise.resolve();
            } else {
                if (this.lockTimeout !== null) {
                    clearTimeout(this.lockTimeout);
                    this.lockTimeout = null;
                }

                errorcb(state);
                return Promise.reject(state);
            }
        });
    }

    unlock()
    {
        clearTimeout(this.lockTimeout);
        this.lockTimeout = null;
        return this.io.unlockDraft(this.key);
    }

    updateFlipcards()
    {
        this.rootAreas.find('.opc-Flipcard').each((i, elm) => elm.updateFlipcardHeight());
    }

    getRevisionList()
    {
        return this.io.getRevisionList(this.key);
    }

    initIframe(jq)
    {
        this.jq        = jq;
        this.rootAreas = this.jq('.opc-rootarea');

        return this.loadDraftPreview();
    }

    loadDraft()
    {
        return this.io.getDraft(this.key)
            .then(pageData => {
                this.id          = pageData.id;
                this.name        = pageData.name;
                this.publishFrom = pageData.publishFrom ? this.decodeDate(pageData.publishFrom) : null;
                this.publishTo   = pageData.publishTo ? this.decodeDate(pageData.publishTo) : null;
                this.url         = pageData.url;
                this.lastModified= pageData.lastModified;
                this.fullUrl     = this.shopUrl + this.url;
            });
    }

    loadDraftPreview()
    {
        return this.io.getDraftPreview(this.key).then(this.onLoad)
    }

    loadRev(revId)
    {
        if(revId === -1) {
            return this.loadPageFromWebStorage();
        } else if(revId === 0) {
            return this.io.getDraftPreview(this.key).then(this.onLoad);
        } else {
            return this.io.getRevisionPreview(revId).then(this.onLoad);
        }
    }

    loadFromData(data)
    {
        return this.io.createPagePreview({areas: data.areas})
            .then(this.onLoad);
    }

    loadFromJSON(json)
    {
        try {
            var data = JSON.parse(json);
        } catch (e) {
            return Promise.reject({error:{message:'JSON data could not be loaded'}});
        }

        return this.loadFromData(data);
    }

    loadFromImport()
    {
        return new Promise(res => {
            this.jq('<input type="file" accept=".json">')
                .on('change', res).click();
        }).then(e => {
            return new Promise(res => {
                this.importReader = new FileReader();
                this.importReader.onload = res;
                this.importReader.readAsText(e.target.files[0]);
            });
        }).then(() => this.loadFromJSON(this.importReader.result));
    }

    loadPageFromWebStorage()
    {
        let pageJson = window.localStorage.getItem(this.getStorageId());

        if(pageJson !== null) {
            this.clear();
            return this.loadFromJSON(pageJson);
        } else {
            return Promise.reject({error:{message:'could not find locally stored draft data'}});
        }
    }

    publicate()
    {
        return this.io.publicateDraft({
            key: this.key,
            publishFrom: this.publishFrom ? this.encodeDate(this.publishFrom) : null,
            publishTo: this.publishTo ? this.encodeDate(this.publishTo) : null,
            name: this.name,
        });
    }

    encodeDate(localDate)
    {
        return moment(localDate, localDateFormat).format(internalDateFormat);
    }

    decodeDate(internalDate)
    {
        return moment(internalDate, internalDateFormat).format(localDateFormat);
    }

    getStorageId()
    {
        return 'opcpage.' + this.key;
    }

    onLoad(preview)
    {
        let areas = this.rootAreas;

        this.clear();

        areas.each((i, area) => {
            area = this.jq(area);
            let areaId = area.data('area-id');
            area.html(preview[areaId]);
            delete preview[areaId];
        });

        this.offscreenAreas = this.jq([]);

        Object.entries(preview).forEach(([areaId, areaContent]) => {
            let area = $('<div class="opc-area opc-rootarea" data-area-id="' + areaId + '">')
                .html(areaContent);
            this.offscreenAreas = this.offscreenAreas.add(area);
        });
    }

    save()
    {
        return this.io.saveDraft(this.toJSON()).then(() => {
            this.lastModified = moment().format(internalDateFormat);
        });
    }

    savePageToWebStorage()
    {
        window.localStorage.setItem(this.getStorageId(), JSON.stringify(this.toJSON()));
    }

    clearPageWebStorage ()
    {
        window.localStorage.removeItem(this.getStorageId());
    }

    hasUnsavedContent ()
    {
        return window.localStorage.getItem(this.getStorageId()) !== null;
    }

    exportAsDownload()
    {
        download(JSON.stringify(this), this.name + '.json', 'application/json');
    }

    clear()
    {
        this.rootAreas.empty();
    }

    toJSON(withDom)
    {
        withDom = withDom || false;

        let result = {
            id:    this.id,
            url:   this.url,
            key:   this.key,
            areas: {}
        };

        let areas = this.rootAreas;

        for(let i=0; i<areas.length; i++) {
            let area     = this.jq(areas[i]);
            let areaData = this.areaToJSON(area, withDom);

            if(areaData.content.length) {
                result.areas[areaData.id] = areaData;
            }
        }

        areas = this.offscreenAreas;

        for(let i=0; i<areas.length; i++) {
            let area     = this.jq(areas[i]);
            let areaData = this.areaToJSON(area, withDom);

            if(areaData.content.length) {
                result.areas[areaData.id] = areaData;
            }
        }

        return result;
    }

    areaToJSON(area, withDom)
    {
        withDom = withDom || false;

        let result   = {id: area.data('area-id'), content: []};
        let portlets = area.children('[data-portlet]');

        portlets.each((i, portlet) => {
            result.content.push(this.portletToJSON(this.jq(portlet), withDom));
        });

        return result;
    }

    portletToJSON(portlet, withDom)
    {
        withDom = withDom || false;

        let data = portlet.data('portlet');

        let result = {
            class: data.class,
            title: data.title,
            properties: data.properties,
            subareas: {},
        };

        let subareas = portlet.find('.opc-area').not(portlet.find('[data-portlet] .opc-area'));

        if (data.class === 'MissingPortlet') {
            result.missingClass = data.missingClass;
        }

        subareas.each((i, subarea) => {
            subarea = this.jq(subarea);
            let subareaData = this.areaToJSON(subarea, withDom);
            result.subareas[subareaData.id] = subareaData;
        });

        result.widthHeuristics = this.computePortletWidthHeuristics(portlet);

        if(withDom) {
            result.elm = portlet;
        }

        return result;
    }

    computePortletWidthHeuristics(portlet)
    {
        let elm             = portlet;
        let widthHeuristics = {xs: 1, sm: 1, md: 1, lg: 1};

        console.log('computePortletWidthHeuristics for', portlet.data('portlet').class);

        while (!elm.is(this.rootAreas) && !elm.is(this.offscreenAreas)) {
            if (elm[0].classList.contains('opc-col')) {
                let areaId = elm.data('area-id').match(/^col-([0-9])+$/)[1];
                elm        = elm.parent();
                let data   = elm.data('portlet');

                if (data) {
                    let cls = data.class;

                    if (cls === 'Row') {
                        let layout = data.properties.layout;
                        let xs     = layout.xs.split('+')[areaId] || 12;
                        let sm     = layout.sm.split('+')[areaId] || 12;
                        let md     = layout.md.split('+')[areaId] || 12;
                        let lg     = layout.lg.split('+')[areaId] || 12;

                        widthHeuristics.xs *= xs / 12;
                        widthHeuristics.sm *= sm / 12;
                        widthHeuristics.md *= md / 12;
                        widthHeuristics.lg *= lg / 12;
                    }
                }
            }

            elm = elm.parent();
        }

        return widthHeuristics;
    }

    removeOffscreenArea(area)
    {
        this.offscreenAreas = this.offscreenAreas.filter((i,elm) => (
            elm !== area[0]
        ));
    }
}