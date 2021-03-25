<nav id="menu_wrapper" class="st-menu st-effect-2">
    <div class="menu-head">
        <div class="avatar"><img src="{$account->cGravatar}" class="avatar-pic" height="95" width="95"></div>
        <div class="user-menu">{$account->cMail}</div>
    </div>

    <ul id="menu">
        {foreach $oLinkOberGruppe_arr as $oLinkOberGruppe}
            <li class="topmenu {if $oLinkOberGruppe@first}topfirst{elseif $oLinkOberGruppe@last}toplast{/if}">
                <p class="menu-link-title"><a href="#" class="parent">
                    <span></span>
                    {$oLinkOberGruppe->cName}
                    <button class="collapse-menu"></button>
                </a></p>
                <ul>
                    {foreach $oLinkOberGruppe->oLinkGruppe_arr as $oLinkGruppe}
                        <li {if $oLinkGruppe@first}class="subfirst"{/if}><a href="#"><span>{$oLinkGruppe->cName}</span></a>
                            {if $oLinkGruppe->oLink_arr|@count > 0}
                                <ul>
                                    {foreach $oLinkGruppe->oLink_arr as $oLink}
                                        <li class="{if $oLink@first}subfirst {if !$oLink->cRecht|permission}noperm{/if}{/if}"><a href="{$oLink->cURL}">{$oLink->cLinkname}</a></li>
                                    {/foreach}
                                </ul>
                            {/if}
                        </li>
                    {/foreach}
                    {foreach $oLinkOberGruppe->oLink_arr as $oLink}
                        <li class="{if $oLink@first}subfirst{/if} {if !$oLink->cRecht|permission}noperm{/if}"><a href="{$oLink->cURL}">{$oLink->cLinkname}</a></li>
                    {/foreach}
                </ul>
            </li>
        {/foreach}
    </ul>
    <button id="navicon" class="menu-icon" data-effect="st-effect-2"></button>
</nav>
