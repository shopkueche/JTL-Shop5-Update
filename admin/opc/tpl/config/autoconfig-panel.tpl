<ul class="nav nav-tabs">
    {foreach $tabs as $tabname => $tab}
        {$tabId = 'conftab'|cat:$tab@index}

        <li class="nav-item">
            <a href="#{$tabId}" data-toggle="tab" class="nav-link {if $tab@index === 0}active{/if}">
                {$tabname}
            </a>
        </li>
    {/foreach}
</ul>

<div class="tab-content">
    {foreach $tabs as $tabname => $tab}
        {$tabId = 'conftab'|cat:$tab@index}

        <div class="tab-pane fade {if $tab@index === 0}show active{/if}" id="{$tabId}">
            <div class="row">
                {$rowWidthAccu = 0}
                {include file='./autoconfig-props.tpl' props=$tab}
            </div>
        </div>
    {/foreach}
</div>