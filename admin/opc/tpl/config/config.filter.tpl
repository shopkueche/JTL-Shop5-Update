<div id="filter-{$propname}" class="product-filter-config">
    <input type="hidden" id="config-{$propname}" name="{$propname}" value="" data-prop-type="json">

    <div class="active-filters">
        <label>{__('activeFilters')}</label>
        <div class="filters-enabled"></div>
    </div>

    <div>
        <div class="available-filters"></div>
    </div>

    <script>
        $(() => {
            let root          = $('#filter-{$propname}');
            let configInput   = $('#config-{$propname}');
            let activeSection = root.find('.active-filters');
            let enabledList   = root.find('.filters-enabled');
            let availableList = root.find('.available-filters');
            let searcherInput = $('#config-{$propdesc.searcher}');
            let filters       = {$propval|json_encode};
            let lastRequest   = null;

            searcherInput.on('input', () => {
                let searchTerm = searcherInput.val().toLowerCase();
                root.find('.filters-section').each((i, sectionItem) => {
                    let count = 0;
                    sectionItem = $(sectionItem);
                    sectionItem.find('.filter-option').each((i, item) => {
                        item = $(item);
                        let text = item.text().toLowerCase();
                        if (text.indexOf(searchTerm) === -1) {
                            item.hide();
                        } else {
                            item.show();
                            count++;
                        }
                    });
                    if(count === 0) {
                        sectionItem.find('.no-options').show()
                    } else {
                        sectionItem.find('.no-options').hide()
                    }
                });
            }).on('keydown', e => {
                if (e.key === 'Escape') {
                    e.stopPropagation();
                }
            });

            updateFilterList();

            function updateFilterList()
            {
                configInput.val(JSON.stringify(filters));
                enabledList.empty();
                searcherInput.val('');

                filters.forEach(filter => {
                    enabledList.append(
                        $('<button type="button">' + filter.name + ' <i class="fas fa-trash-alt"></i></button>')
                            .on('click', () => unselectFilter(filter))
                    );
                });

                if (filters.length === 0) {
                    activeSection.hide();
                } else {
                    if (filters.length > 1) {
                        enabledList.prepend(
                            $(
                                '<button type="button" class="restore-all">' +
                                'Alle zurücksetzen <i class="fas fa-trash-alt"></i></button>'
                            ).on('click', resetAll)
                        );
                    }

                    activeSection.show();
                }

                if (lastRequest) {
                    lastRequest.jqxhr.abort();
                }

                root.find('button').prop('disabled', true);
                lastRequest = opc.io.getFilterList('{$propname}', filters);

                lastRequest.then(html => {
                    availableList.html(html);
                    root.find('.filter-option').each((i, optionBtn) => {
                        optionBtn = $(optionBtn);
                        optionBtn.on('click', () => selectFilter(optionBtn.data('filter')));
                    });
                    root.find('button').prop('disabled', false);
                });
            }

            function selectFilter(filter)
            {
                filters.push(filter);
                updateFilterList();
            }

            function unselectFilter(filter)
            {
                filters = filters.filter(elm => elm !== filter);
                updateFilterList();
            }

            function resetAll()
            {
                filters = [];
                updateFilterList();
            }
        });
    </script>
</div>
