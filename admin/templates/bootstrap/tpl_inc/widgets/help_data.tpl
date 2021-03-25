{foreach $oHelp_arr as $oHelp}
    <li>
        <p>
            {if $oHelp->cIconURL|strlen > 0}
                <img src="{$oHelp->cIconURL|urldecode}" alt="" title="{$oHelp->cTitle}" />
            {/if}
            <a href="{$oHelp->cURL}" title="{$oHelp->cTitle}|utf8_decode" target="_blank" rel="noopener">
                {$oHelp->cTitle|utf8_decode|truncate:50:'...'}
            </a>
        </p>
    </li>
{/foreach}
