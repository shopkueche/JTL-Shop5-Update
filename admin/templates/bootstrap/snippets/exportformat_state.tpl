{if $exportformat->nFehlerhaft === -1}
    <span class="label text-warning">{__('untested')}</span>
{elseif $exportformat->nFehlerhaft === 1}
    <i class="fal fa-times text-danger"></i>
{else}
    <i class="fal fa-check text-success"></i>
{/if}
