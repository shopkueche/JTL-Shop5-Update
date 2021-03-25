{$license = $data->getLicense()}
<h2>{__('License')}</h2>
<table class="table-striped table">
    {if $data|is_a:JTL\License\Struct\ExpiredExsLicense::class}
        <div class="alert alert-danger">{__('No license found.')}</div>
    {else}
        <tbody>
            <tr>
                <th>{__('Vendor')}</th>
                <td><a href="{$data->getVendor()->getHref()}" rel="noopener"><i class="fas fa-external-link"></i> {$data->getVendor()->getName()}</a></td>
            </tr>
            <tr>
                <th>{__('Key')}</th>
                <td>{$license->getKey()}</td>
            </tr>
            <tr>
                <th>{__('Date created')}</th>
                <td>{$license->getCreated()->format('d.m.Y')}</td>
            </tr>
            {if $license->getValidUntil() !== null}
            <tr>
                <th>{__('Valid until')}</th>
                <td>{$license->getValidUntil()->format('d.m.Y')}</td>
            </tr>
            {elseif $license->getSubscription()->getValidUntil() !== null}
            <tr>
                <th>{__('Valid until')}</th>
                <td>{$license->getSubscription()->getValidUntil()->format('d.m.Y')}</td>
            </tr>
            {/if}
        </tbody>
    {/if}
</table>
<p class="mb-0 mt-2">
    {foreach $data->getLinks() as $link}
        {if $link->getRel() !== 'clearBinding'}
            <a href="{$link->getHref()}" rel="noopener" class="btn btn-default">{__($link->getRel())}</a>
        {/if}
    {/foreach}
</p>
