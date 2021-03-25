{foreach $validPageTypes as $validPagetype}
    <option value="{$validPagetype.pageID}" {if $nPage == {$validPagetype.pageID}}selected="selected"{/if}>
        {if $validPagetype.pageID === 0}
            {__('allPages')}
        {else}
            {$validPagetype.pageName}
        {/if}
    </option>
{/foreach}
