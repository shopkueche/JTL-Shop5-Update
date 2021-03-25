{if $licenseItemUpdates->count() > 0}
    {$notifyTypes = [0 => 'info', 1 => 'warning', 2 => 'danger']}
    <a href="#" class="nav-link text-primary px-2" data-toggle="dropdown">
        <span class="fa-layers fa-fw has-notify-icon stack-refresh">
            <span class="fas fa-refresh"></span>
            <span class="fa-stack">
                <span class="fas fa-circle fa-stack-2x text-warning"></span>
                <strong class="fa-stack-1x">{$licenseItemUpdates->count()}</strong>
            </span>
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg" role="main">
        <span class="dropdown-header">{__('Updates available')}</span>
        <div class="dropdown-divider"></div>
        {foreach $licenseItemUpdates as $item}
            <div class="dropdown-item-text">
                <span class="icon-text-indent">
                    <div><i class="fa fa-circle text-info" aria-hidden="true"></i></div>
                    <a href="{$adminURL}/licenses.php#license-item-{$item->getID()}">
                        <span class="item-name">{$item->getName()} </span> <span class="badge badge-info">
                            {$item->getReferencedItem()->getInstalledVersion()} &rarr; {$item->getReferencedItem()->getMaxInstallableVersion()}
                        </span>
                    </a>
                </span>
            </div>
        {/foreach}
    </div>
{/if}
