<{$params.tag->getValue()} role="separator" class="dropdown-divider {$params.class->getValue()}"
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style->getValue()}"{/if}
>
</{$params.tag->getValue()}>
