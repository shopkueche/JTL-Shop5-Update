<div>
    <div class="card">
        {if isset($setToken) && $setToken === true}
            {form}
                <input type="hidden" name="action" value="savetoken">
                <div class="card-header">
                    <div class="heading-body">
                        {__('Enter token')}
                    </div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="sub-heading">Token</div>
                    <textarea class="form-control" name="token"></textarea>
                    <div class="sub-heading">Code</div>
                    <input type="text" class="form-control" name="code" />
                </div>
                <div class="save-wrapper">
                    <button class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                </div>
            {/form}
        {else}
            {form}
                <div class="card-header">
                    <div class="heading-body">
                        {__('Overview')}
                    </div>
                    <div class="heading-right">
                        {if $hasAuth}
                            <button name="action" value="revoke" class="btn btn-default">
                                <i class="fas fa-unlink"></i> {__('unlink')}
                            </button>
                        {/if}
                    </div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="row">
                        {if $hasAuth}
                            <div class="col-md-4 border-right">
                                <div class="text-center">
                                    <h2>{$licenseItemUpdates->count()}</h2>
                                    <p>{__('updates available')}</p>
                                </div>
                            </div>
                            <div class="col-md-4 border-right">
                                <div class="text-center">
                                    <h2>{$licenses->count()}</h2>
                                    <p>{__('Licensed items')}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h2>{$lastUpdate|date_format:"%d.%m.%Y %H:%M:%S"}</h2>
                                    <p>{__('last update')}</p>
                                    <button class="btn btn-default btn-block" id="recheck" name="action" value="recheck">
                                        <i class="fas fa-refresh"></i> {__('Refresh')}
                                    </button>
                                </div>
                            </div>
                        {else}
                            <div class="col-12">
                                <div class="alert alert-default" role="alert">{__('storeNotLinkedDesc')}</div>
                            </div>
                            <div class="col-sm-6 col-xl-auto mb-2">
                                <button name="action" value="redirect" class="btn btn-primary btn-block">
                                    <i class="fas fa-link"></i> {__('storeLink')}
                                </button>
                            </div>
                            <div class="col-sm-6 col-xl-auto mb-2">
                                <button name="action" value="entertoken" class="btn btn-secondary btn-block">
                                    {__('Manually enter token')}
                                </button>
                            </div>
                            <div class="col-sm-6 col-xl-auto ml-auto mb-2">
                                <a href="{__('extensionStoreURL')}" class="btn btn-outline-primary btn-block ml-auto" target="_blank">
                                    {__('btnExploreExtensionStore')}
                                </a>
                            </div>
                        {/if}
                    </div>
                </div>
            {/form}
        {/if}
    </div>
</div>
