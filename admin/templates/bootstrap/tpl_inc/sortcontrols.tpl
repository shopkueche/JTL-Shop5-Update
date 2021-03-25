<script>
    function pagiResort (pagiId, nSortBy, nSortDir)
    {
        $('#' + pagiId + '_nSortByDir').val(nSortBy * 2 + nSortDir);
        $('form#' + pagiId).submit();
        return false;
    }
</script>

{function sortControls}
    {if $pagination->getSortBy() !== $nSortBy}
        <a href="#" onclick="return pagiResort('{$pagination->getId()}', {$nSortBy}, 0);"><i class="fa fa-unsorted"></i></a>
    {elseif $pagination->getSortDirSpecifier() === 'DESC'}
        <a href="#" onclick="return pagiResort('{$pagination->getId()}', {$nSortBy}, 0);"><i class="fa fa-sort-desc"></i></a>
    {elseif $pagination->getSortDirSpecifier() === 'ASC'}
        <a href="#" onclick="return pagiResort('{$pagination->getId()}', {$nSortBy}, 1);"><i class="fa fa-sort-asc"></i></a>
    {/if}
{/function}
