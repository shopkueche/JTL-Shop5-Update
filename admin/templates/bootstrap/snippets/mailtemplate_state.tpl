{if $template->getHasError()}
    <span class="label text-danger">{__('faulty')}</span>
{elseif $template->getActive() === false}
    <span class="fal fa-times text-danger"></span>
{elseif $template->getSyntaxCheck() === \JTL\Mail\Template\Model::SYNTAX_NOT_CHECKED}
    <span class="label text-warning">{__('untested')}</span>
{else}
    <span class="fal fa-check text-success"></span>
{/if}