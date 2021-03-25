class PageTree
{
    constructor(page, iframe, gui)
    {
        bindProtoOnHandlers(this);

        this.page     = page;
        this.iframe   = iframe;
        this.gui      = gui;
        this.selected = undefined;
    }

    init()
    {
        installGuiElements(this, ['pageTreeView']);
    }

    setSelected(portlet)
    {
        if(this.selected) {
            $(this.selected[0].treeItem).removeClass('selected');
        }

        this.selected = portlet;

        if(this.selected) {
            $(this.selected[0].treeItem).addClass('selected');
            this.expandTo(portlet);
        }
    }

    expandTo(portlet)
    {
        let treeItem = $(portlet[0].treeItem);

        while(treeItem.length > 0) {
            treeItem = treeItem.parent().closest('li');
            treeItem.addClass('expanded');
        }
    }

    render()
    {
        let rootAreas = this.page.rootAreas;
        let jq        = rootAreas.constructor;
        let ul        = $('<ul>');

        rootAreas.each((i, area) => {
            ul.append(this.renderArea(jq(area)));
        });

        this.pageTreeView.empty().append(ul);

        if(this.page.offscreenAreas.length) {
            ul = $('<ul>');

            this.page.offscreenAreas.each((i, area) => {
                ul.append(this.renderArea(jq(area), false, true));
            });

            let offscreenDivider = $('<div class="offscreenDivider">')
                .html(this.gui.messages.offscreenAreasDivider);
            this.pageTreeView.append(offscreenDivider);
            this.pageTreeView.append(ul);
        }
    }

    renderBaseItem(text, click, cls = '', area = null, offscreenArea = false)
    {
        let expander = $('<a href="#" class="item-expander">');
        let item     = $('<a href="#" class="item-label">');
        let head     = $('<div class="item-head">');
        let li       = $('<li class="' + cls + '">');

        expander.append('<i class="fas fa-fw fa-chevron-right">');
        expander.append('<i class="fas fa-fw fa-chevron-down">');
        item.append(' ' + text);
        head.append(expander).append(item);
        li.append(head);

        function expand(e) {
            e.preventDefault();
            li.toggleClass('expanded');
        }

        expander.on('click', expand);

        if(click) {
            item.dblclick(expand);
        }

        item.on('click', e => {
            e.preventDefault();

            if(click) {
                click();
            } else {
                li.toggleClass('expanded');
            }
        });

        if(offscreenArea) {
            let copybtn         = $('<a href="#" data-toggle="dropdown">');
            let delbtn          = $('<a href="#" data-toggle="dropdown">');
            let dropdownMenu    = $('<div class="dropdown-menu opc-dropdown-menu" style="width: 250px">');
            let dropdown        = $('<div class="item-copybtn opc-dropdown">');
            let deldropdownMenu = $('<div class="dropdown-menu opc-dropdown-menu" style="width: 250px">');
            let deldropdown     = $('<div class="item-copybtn opc-dropdown">');

            delbtn.append('<i class="fas fa-fw fa-trash">');
            copybtn.append('<i class="fas fa-fw fa-arrow-right">');
            dropdown.append(copybtn).append(dropdownMenu);
            deldropdown.append(delbtn).append(deldropdownMenu);
            head.append(dropdown).append(deldropdown);
            copybtn.attr('title', this.gui.messages.btnTitleCopyArea);
            delbtn.attr('title', this.gui.messages.yesDeleteArea);

            copybtn.click(() => {
                dropdownMenu.empty();

                this.page.rootAreas.each((i, targetArea) => {
                    targetArea = $(targetArea);

                    if(targetArea.find('[data-portlet]').length === 0) {
                        let areaId = targetArea.data('area-id');
                        let btn    = $('<button type="button" class="opc-dropdown-item">' + areaId + '</button>');

                        btn.on('click', () => {
                            targetArea.html(area.html());
                            this.iframe.updateDropTargets();
                            this.removeOffscreenArea(area);
                        });

                        dropdownMenu.append(btn);
                    }
                });
            });

            let yes = $('<button type="button" class="opc-dropdown-item">' +
                this.gui.messages.yesDeleteArea + '!</button>')
                .on('click', () => {
                    this.removeOffscreenArea(area);
                });

            let no  = $('<button type="button" class="opc-dropdown-item">' +
                this.gui.messages.Cancel + '</button>');

            deldropdownMenu.append(yes).append(no);
        }

        return li;
    }

    renderArea(area, expanded = false, offscreenArea = false)
    {
        let portlets = area.children('[data-portlet]');
        let jq       = area.constructor;
        let data     = area.data('area-id');
        let ul       = $('<ul>');
        let li       = this.renderBaseItem('' + data + '', null, 'area-item', area, offscreenArea);

        portlets.each((i, portlet) => {
            portlet = jq(portlet);
            ul.append(this.renderPortlet(portlet));
        });

        if(portlets.length === 0) {
            li.addClass('leaf');
        } else if(expanded) {
            li.addClass('expanded');
        }

        area[0].treeItem = li[0];
        li[0].area       = area[0];

        return li.append(ul);
    }

    renderPortlet(portlet)
    {
        let subareas = portlet.find('.opc-area').not(portlet.find('[data-portlet] .opc-area'));
        let jq       = portlet.constructor;
        let data     = portlet.data('portlet');
        let ul       = $('<ul>');

        let li = this.renderBaseItem(data.class, () => {
            this.iframe.setSelected(portlet);
        }, 'portlet-item');

        subareas.each((i, area) => {
            area = jq(area);
            ul.append(this.renderArea(area));
        });

        if(subareas.length === 0) {
            li.addClass('leaf');
        }

        portlet[0].treeItem = li[0];
        li[0].portlet       = portlet[0];

        return li.append(ul);
    }

    updateArea(area)
    {
        let treeItem = $(area[0].treeItem);
        let expanded = treeItem.hasClass('expanded');

        $(area[0].treeItem).replaceWith(this.renderArea(area, expanded));
    }

    removeOffscreenArea(area)
    {
        this.page.removeOffscreenArea(area);
        this.render();
        this.gui.setUnsaved(true, true);
        this.gui.updatePagetreeBtn();
    }
}
