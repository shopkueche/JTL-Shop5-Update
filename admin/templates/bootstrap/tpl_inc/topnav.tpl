<ul id="topmenu">
    {foreach $oLinkOberGruppe_arr as $oLinkOberGruppe}
        <li class="topmenu {if $oLinkOberGruppe@first}topfirst{elseif $oLinkOberGruppe@last}toplast{/if}">
            <a href="#" class="parent"><span class="link-icon"></span><span class="link-text">{$oLinkOberGruppe->cName}</span></a>
            <ul>
                {foreach $oLinkOberGruppe->oLinkGruppe_arr as $oLinkGruppe}
                    <li {if $oLinkGruppe@first}class="subfirst"{/if}>
                        <a href="#"><span>{$oLinkGruppe->cName}</span></a>
                        {if $oLinkGruppe->oLink_arr|@count > 0}
                            <ul>
                                {foreach $oLinkGruppe->oLink_arr as $oLink}
                                    <li class="{if $oLink@first}subfirst {if !$oLink->cRecht|permission}noperm{/if}{/if}">
                                        <a href="{$oLink->cURL}">{$oLink->cLinkname}</a></li>
                                {/foreach}
                            </ul>
                        {/if}
                    </li>
                {/foreach}
                {foreach $oLinkOberGruppe->oLink_arr as $oLink}
                    <li class="{if $oLink@first}subfirst{/if} {if !$oLink->cRecht|permission}noperm{/if}">
                        <a href="{$oLink->cURL}">{$oLink->cLinkname}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/foreach}
</ul>