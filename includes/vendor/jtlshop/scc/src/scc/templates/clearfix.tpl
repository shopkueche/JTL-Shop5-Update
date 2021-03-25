
{if $params['visible-size']->hasValue()}
    {$visibleSize = $params['visible-size']->getValue()}
    {if $visibleSize === 'xs'}
        {$nextSize = 'sm'}
    {elseif $visibleSize === 'sm'}
        {$nextSize = 'md'}
    {elseif $visibleSize === 'md'}
        {$nextSize = 'lg'}
    {elseif $visibleSize === 'lg'}
        {$nextSize = 'xl'}
    {elseif $visibleSize === 'xl'}
        {$nextSize = null}
    {/if}
    {if $visibleSize === 'xs'}
        <div class="clearfix d-block d-{$nextSize}-none"></div>
    {elseif !empty($nextSize)}
        <div class="clearfix d-none d-{$visibleSize}-block d-{$nextSize}-none"></div>
    {else}
        <div class="clearfix d-none d-{$visibleSize}-block"></div>
    {/if}
{else}
    <div class="clearfix"></div>
{/if}
