<div id="shoptemplate-overview">
    <div class="alerts">
        <div class="alert alert-danger" id="alert-upload-error" style="display:none"></div>
        <div class="alert alert-success" id="alert-upload-success" style="display:none"></div>
    </div>
    <div class="card">
        <div class="table-responsive card-body">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th></th>
                    <th>{__('Name')}</th>
                    <th class="text-center">{__('status')}</th>
                    <th class="text-center">{__('version')}</th>
                    <th class="text-center">{__('shopversion')}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {foreach $listingItems as $listingItem}
                    {include file='tpl_inc/shoptemplate_listing_item.tpl'}
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
