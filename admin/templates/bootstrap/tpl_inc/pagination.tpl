{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

{assign var=cUrlAppend value=$cParam_arr|http_build_query}

{if isset($cAnchor)}
    {assign var=cUrlAppend value=$cUrlAppend|cat:'#'|cat:$cAnchor}
{/if}

{assign var=bItemsAvailable value=$pagination->getItemCount() > 0}
{assign var=bMultiplePages value=$pagination->getPageCount() > 1}
{assign var=bSortByOptions value=$pagination->getSortByOptions()|@count > 0}

{function pageButtons}
    <span class="font-weight-bold d-block mb-3">
        {if $bMultiplePages}
            {__('entries')} {$pagination->getFirstPageItem() + 1}
            - {$pagination->getFirstPageItem() + $pagination->getPageItemCount()}
            {__('of')} {$pagination->getItemCount()}
        {else}
            {__('allEntries')}
        {/if}
    </span>
    <nav aria-label="Page navigation example">
    {if $bMultiplePages}
        <ul class="pagination justify-content-between justify-content-md-start mb-5 mb-md-0">
            <li class="page-item">
                <a class="page-link" {if $pagination->getPrevPage() != $pagination->getPage()}href="?{$pagination->getId()}_nPage={$pagination->getPrevPage()}&{$cUrlAppend}"{/if}>
                    <span class="fal fa-long-arrow-left"></span>
                </a>
            </li>
            {if $pagination->getLeftRangePage() > 0}
                <li class="page-item">
                    <a class="page-link" href="?{$pagination->getId()}_nPage=0&{$cUrlAppend}">1</a>
                </li>
            {/if}
            {if $pagination->getLeftRangePage() > 1}
                <li class="page-item">
                    <span class="page-text">&hellip;</span>
                </li>
            {/if}
            {for $i=$pagination->getLeftRangePage() to $pagination->getRightRangePage()}
                <li class="page-item{if $pagination->getPage() == $i} active{/if}">
                    <a class="page-link" href="?{$pagination->getId()}_nPage={$i}&{$cUrlAppend}">{$i+1}</a>
                </li>
            {/for}
            {if $pagination->getRightRangePage() < $pagination->getPageCount() - 2}
                <li class="page-item">
                    <span class="page-text">&hellip;</span>
                </li>
            {/if}
            {if $pagination->getRightRangePage() < $pagination->getPageCount() - 1}
                <li class="page-item">
                    <a class="page-link" href="?{$pagination->getId()}_nPage={$pagination->getPageCount() - 1}&{$cUrlAppend}">{$pagination->getPageCount()}</a>
                </li>
            {/if}
            <li class="page-item">
                <a class="page-link" {if $pagination->getNextPage() != $pagination->getPage()}href="?{$pagination->getId()}_nPage={$pagination->getNextPage()}&{$cUrlAppend}"{/if}>
                    <span class="fal fa-long-arrow-right"></span>
                </a>
            </li>
        </ul>
    {else}
        <ul class="pagination">
            <li>
                <a>{$pagination->getItemCount()}</a>
            </li>
        </ul>
    {/if}
    </nav>
{/function}

{function itemsPerPageOptions}
    <label for="{$pagination->getId()}_nItemsPerPage">{__('entriesPerPage')}</label>
    <select class="custom-select" name="{$pagination->getId()}_nItemsPerPage" id="{$pagination->getId()}_nItemsPerPage"
            onchange="this.form.submit()">
        {foreach $pagination->getItemsPerPageOptions() as $nItemsPerPageOption}
            <option value="{$nItemsPerPageOption}"{if $pagination->getItemsPerPage() == $nItemsPerPageOption} selected="selected"{/if}>
                {if $nItemsPerPageOption === -1}
                    {__('all')}
                {else}
                    {$nItemsPerPageOption}
                {/if}
            </option>
        {/foreach}
    </select>
{/function}

{function sortByDirOptions}
    <label for="{$pagination->getId()}_nSortByDir">{__('sorting')}</label>
    <select class="custom-select" name="{$pagination->getId()}_nSortByDir" id="{$pagination->getId()}_nSortByDir"
            onchange="this.form.submit()">
        {foreach $pagination->getSortByOptions() as $i => $cSortByOption}
            <option value="{$i * 2}"
                    {if $i * 2 == $pagination->getSortByDir()} selected="selected"{/if}>
                {$cSortByOption[1]} {__('ascending')}
            </option>
            <option value="{$i * 2 + 1}"
                    {if $i * 2 + 1 == $pagination->getSortByDir()} selected="selected"{/if}>
                {$cSortByOption[1]} {__('descending')}
            </option>
        {/foreach}
    </select>
{/function}

{if $bItemsAvailable}
    <div class="pagination-toolbar">
        <form action="{if isset($cAnchor)}#{$cAnchor}{/if}" method="get" name="{$pagination->getId()}" id="{$pagination->getId()}">
            <div class="row mb-5">
                <div class="col-12 col-md-4">
                    {pageButtons}
                </div>
                {foreach $cParam_arr as $cParamName => $cParamValue}
                    <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
                {/foreach}
                <div class="col-12 col-md-4 col-lg-3 col-xl-2 ml-lg-auto">
                    <div class="form-group">
                        {itemsPerPageOptions}
                    </div>
                </div>
                {if $bSortByOptions}
                    <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                        <div class="form-group">
                            {sortByDirOptions}
                        </div>
                    </div>
                {/if}
            </div>
        </form>
    </div>
{/if}
