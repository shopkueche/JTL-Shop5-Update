{$referencedItem = $license->getReferencedItem()}
<div id="license-item-{$license->getID()}-{$license->getLicense()->getType()}">
    {if $license->isInApp()}
        {$avail = $license->getReleases()->getAvailable()}
        {if $avail !== null}
            <span class="item-available badge badge-info">
                {__('Version %s available', $avail->getVersion())}
            </span>
        {/if}
        <p class="mb-0 mt-2">{__('Managed by %s')|sprintf:$license->getParent()->getName()}</p>
    {elseif $referencedItem !== null}
        {$licData = $license->getLicense()}
        {$subscription = $licData->getSubscription()}
        {$disabled = $licData->isExpired() || $subscription->isExpired()}
        {if isset($licenseErrorMessage)}
            <div class="alert alert-danger">
                {__($licenseErrorMessage)}
            </div>
        {/if}
        {$installedVersion = $referencedItem->getInstalledVersion()}
        {if $installedVersion === null}
            {$avail = $license->getReleases()->getAvailable()}
            {if $avail === null}
                {$disabled = true}
                <i class="far fa-circle"></i> <span class="badge badge-danger">{__('No version available')}</span>
            {else}
                <i class="far fa-circle"></i> <span class="item-available badge badge-info">
                    {__('Version %s available', $avail->getVersion())}
                </span>
            {/if}
            {form method="post" class="mt-2{if !$disabled} install-item-form{/if}"}
                <input type="hidden" name="action" value="install">
                <input type="hidden" name="item-type" value="{$license->getType()}">
                <input type="hidden" name="item-id" value="{$license->getID()}">
                <input type="hidden" name="license-type" value="{$license->getLicense()->getType()}">
                <button{if $disabled} disabled{/if} class="btn btn-default btn-sm install-item" name="action" value="install">
                    <i class="fa fa-share"></i> {__('Install')}
                </button>
            {/form}
        {else}
            <i class="far fa-check-circle"></i> {$installedVersion}{if $referencedItem->isActive() === false} {__('(disabled)')}{/if}
        {/if}
        {if $referencedItem->hasUpdate()}
            <span class="update-available badge badge-success">
                {__('Update to version %s available', $referencedItem->getMaxInstallableVersion())}
            </span>
            {form method="post" class="mt-2{if !$disabled} update-item-form{/if}"}
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="item-type" value="{$license->getType()}">
                <input type="hidden" name="license-type" value="{$license->getLicense()->getType()}">
                <input type="hidden" name="item-id" value="{$license->getID()}">
                <input type="hidden" name="exs-id" value="{$license->getExsID()}">
                <button{if $disabled} disabled{/if} class="btn btn-default btn-sm update-item" name="action" value="update">
                    <i class="fas fa-refresh"></i> {__('Update')}
                </button>
            {/form}
        {/if}
    {else}
        <i class="far fa-circle"></i>
    {/if}
</div>
