{foreach $filters as $filter}
    <label>{__($filter.name)}</label>
    <div class="filters-section">
        <div class="no-options" style="display: none">Keine Optionen</div>
        <div class="filters-section-inner">
            {foreach $filter.options as $option}
                <button type="button" class="filter-option" data-filter="{$option|json_encode|htmlentities}"
                        title="{$option.name} ({$option.count})">
                    <i class="far fa-square"></i> {$option.name} ({$option.count})
                </button>
            {/foreach}
        </div>
    </div>
{/foreach}