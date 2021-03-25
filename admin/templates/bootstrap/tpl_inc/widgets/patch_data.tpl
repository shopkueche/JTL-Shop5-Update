{if count($oPatch_arr) > 0}
    {foreach $oPatch_arr as $oPatch}
        <li>
            {if $oPatch->cIconURL|strlen > 0}
                <img src="{$oPatch->cIconURL|urldecode}" alt="" title="{$oPatch->cTitle}" />
            {/if}
            <p><a href="{$oPatch->cURL}" title="{$oPatch->cTitle}" target="_blank" rel="noopener">
                {$oPatch->cTitle|truncate:50:'...'}
                {$oPatch->cDescription}
            </a></p>
        </li>
    {/foreach}
{else}
    <div class="alert alert-info">{__('noPatchesATM')}</div>
{/if}
