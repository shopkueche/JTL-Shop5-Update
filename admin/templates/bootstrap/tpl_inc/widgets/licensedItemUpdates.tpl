    {if $hasAuth}
        <div class="row">
            <div class="col-md-4 border-right">
                <div class="text-center">
                    <h2>{$licenseItemUpdates->count()}{if $securityFixes > 0} <i class="fa fa-warning" data-toggle="tooltip" data-placement="top" title="{n__('Includes %d security fix', 'Includes %d security fixes', $securityFixes)|sprintf:$securityFixes}"></i>{/if}</h2>
                    <p>{n__('Update available', 'Updates available', $licenseItemUpdates->count())}</p>
                </div>
            </div>
            <div class="col-md-4 border-right">
                <div class="text-center">
                    <h2>{$licenses->count()}{if $aboutToExpire->count() > 0} <i class="fa fa-warning" data-toggle="tooltip" data-placement="top" title="{n__('%d license about to expire', '%d licenses about to expire', $aboutToExpire->count())|sprintf:($aboutToExpire->count())}"></i>{/if}</h2>
                    <p>{n__('Licensed item', 'Licensed items', $licenses->count())}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <h2>{$expirations}</h2>
                    <p>{n__('Expired subscription', 'Expired subscriptions', $expirations)}</p>
                </div>
            </div>
        </div>

        <hr class="mb-4">

        {if $lastPurchases->count() > 0}
            <div class="row">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th class="w-75">{__('Last purchases')}</th>
                        <th class="w-25">{__('Date')}</th>
                    </tr>
                    </thead>
                    {foreach $lastPurchases as $license}
                        <tr>
                            <td>
                                <a href="{$adminURL}/licenses.php#license-item-{$license->getID()}">
                                    {$license->getName()}
                                </a> {if $license->getLicense()->isBound()}<span class="badge badge-primary">{__('bound')}</span>{else}<span class="badge badge-secondary">{__('unbound')}</span>{/if}</td>
                            <td>{$license->getLicense()->getCreated()->format('d.m.Y')}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        {/if}
        {if $aboutToExpire->count() > 0}
            <div class="row">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th class="w-75">{n__('%d license about to expire', '%d licenses about to expire', $aboutToExpire->count())|sprintf:($aboutToExpire->count())}</th>
                        <th class="w-25">{__('Expiration')}</th>
                    </tr>
                    </thead>
                    {foreach $aboutToExpire as $license}
                        {$days = $license->getLicense()->getDaysRemaining()}
                        <tr>
                            <td>
                                <a href="{$adminURL}/licenses.php#license-item-{$license->getID()}">
                                    {$license->getName()}
                                </a>{if $days > 0} <span class="badge badge-danger">{n__('%d day remaining', '%d days remaining', $days)|sprintf:$days}</span>{/if}
                            </td>
                            <td>{$license->getLicense()->getValidUntil()->format('d.m.Y')}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        {/if}
        {if $licenseItemUpdates->count() > 0}
            <div class="row">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th class="w-75">{n__('Update available', 'Updates available', $licenseItemUpdates->count())}</th>
                        <th class="w-25">{__('Version')}</th>
                    </tr>
                    </thead>
                    {foreach $licenseItemUpdates as $license}
                        {$avail = $license->getReleases()->getAvailable()}
                        <tr>
                            <td>
                                <a href="{$adminURL}/licenses.php#license-item-{$license->getID()}">
                                    {$license->getName()}
                                </a> {if $avail !== null && $avail->includesSecurityFixes()} <span class="badge badge-warning">{__('Security update')}</span> {/if}</td>
                            <td>
                                <p class="badge badge-secondary">{$license->getReferencedItem()->getInstalledVersion()}</p>
                                &rarr;
                                <p class="badge badge-primary">{$license->getReferencedItem()->getMaxInstallableVersion()}</p>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        {/if}
        {if $testLicenses->count() > 0}
            <div class="row">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th class="w-75">{__('Test licenses')}</th>
                        <th class="w-25">{__('Expiration')}</th>
                    </tr>
                    </thead>
                    {foreach $testLicenses as $license}
                        <tr>
                            <td>
                                <a href="{$adminURL}/licenses.php#license-item-{$license->getID()}">
                                    {$license->getName()}
                                </a>
                            <td>{$license->getLicense()->getValidUntil()->format('d.m.Y')}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        {/if}
    {else}
        <div class="alert alert-default" role="alert">{__('storeNotLinkedDesc')}</div>
    {/if}
<a href="{__('extensionStoreURL')}" class="btn btn-outline-primary btn-block my-3" target="_blank">
    {if $hasAuth}{__('btnAdditionalExtensionStore')}{else}{__('btnExploreExtensionStore')}{/if}
</a>
<hr class="mb-3">
<p class="text-right"><small>{__('last update')} {$lastUpdate|date_format:'%d.%m.%Y %H:%M:%S'}</small></p>
