{$licData = $license->getLicense()}
{$subscription = $licData->getSubscription()}
{if $subscription->isExpired() && $subscription->getValidUntil() !== null}
    <span class="badge badge-danger">
        {__('Subscription expired on %s', $subscription->getValidUntil()->format('d.m.Y'))}
    </span>
{elseif $licData->isExpired()}
    <span class="badge badge-danger">{__('License expired on %s', $licData->getValidUntil()->format('d.m.Y'))}</span>
{elseif $subscription->isExpired() === false && $subscription->getValidUntil() !== null}
    {if $subscription->getDaysRemaining() < 28}
        <span class="badge badge-warning">
            {if $licData->getType() === 'test'}
                {__('Warning: License only valid until %s', $licData->getValidUntil()->format('d.m.Y'))}
            {else}
                {__('Warning: Subscription only valid until %s', $subscription->getValidUntil()->format('d.m.Y'))}
            {/if}
        </span>
    {else}
        <span class="badge badge-success">
            {__('Subscription valid until %s', $subscription->getValidUntil()->format('d.m.Y'))}
        </span>
    {/if}
{elseif $licData->getValidUntil() !== null}
    {if $licData->getDaysRemaining() < 28}
        <span class="badge badge-warning">
            {__('Warning: License only valid until %s', $licData->getValidUntil()->format('d.m.Y'))}
        </span>
    {else}
        <span class="badge badge-success">
            {__('License valid until %s', $licData->getValidUntil()->format('d.m.Y'))}
        </span>
    {/if}
{else}
    <span class="badge badge-success">{__('Valid')}</span>
{/if}
{foreach $license->getLinks() as $link}
    {if $link->getRel() === 'extendLicense' && ($license->hasSubscription() || $license->hasLicense())}
        <p class="mt-2 mb-0">
        {form class='extend-license-form mt-2'}
            <input type="hidden" name="action" value="extendLicense">
            <input type="hidden" name="url" value="{$link->getHref()}">
            <input type="hidden" name="method" value="{$link->getMethod()|default:'POST'}">
            <input type="hidden" name="exsid" value="{$license->getExsID()}">
            <input type="hidden" name="key" value="{$license->getLicense()->getKey()}">
            <button type="submit" class="btn btn-sm btn-primary extend-license"
                    data-link="{$link->getHref()}"
                    href="#"
                    title="{if $licData->getType() === 'test'}{__('extendTestLicense')}{elseif $license->hasSubscription()}{__('extendSubscription')}{else}{__('extendLicense')}{/if}">
                <i class="fa fa-link"></i> {if $licData->getType() === 'test'}{__('extendTestLicense')}{elseif $license->hasSubscription()}{__('extendSubscription')}{else}{__('extendLicense')}{/if}
            </button>
        {/form}
        </p>
    {elseif $link->getRel() === 'upgradeLicense'}
        <p class="mt-2 mb-0">
        {form class='upgrade-license-form mt-2'}
            <input type="hidden" name="action" value="upgradeLicense">
            <input type="hidden" name="url" value="{$link->getHref()}">
            <input type="hidden" name="method" value="{$link->getMethod()|default:'POST'}">
            <input type="hidden" name="exsid" value="{$license->getExsID()}">
            <input type="hidden" name="key" value="{$license->getLicense()->getKey()}">
            <button type="submit" class="btn btn-sm btn-primary upgrade-license"
                    data-link="{$link->getHref()}"
                    href="#"
                    title="{__($link->getRel())}">
                <i class="fa fa-link"></i> {__($link->getRel())}
            </button>
        {/form}
        </p>
    {/if}
{/foreach}
