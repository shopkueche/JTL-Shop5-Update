{if $params['visible-size']->hasValue()}
    <div class="clearfix visible-{$params['visible-size']->getValue()}-block"></div>
{else}
    <div class="clearfix"></div>
{/if}
