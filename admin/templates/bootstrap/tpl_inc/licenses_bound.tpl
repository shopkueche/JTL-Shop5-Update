<div id="bound-licenses">
    {if isset($extendErrorMessage)}
        <div class="alert alert-danger">
            {__($extendErrorMessage)}
        </div>
    {/if}
    {if isset($extendSuccessMessage)}
        <div class="alert alert-success">
            {__($extendSuccessMessage)}
        </div>
    {/if}
    <div class="card">
        <div class="card-header">
            {__('Bound licenses')}
            <hr class="mb-n3">
        </div>
        <div class="card-body">
        {if $licenses->getActive()->count() > 0}
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>{__('ID')}</th>
                        <th>{__('Name')}</th>
                        <th>{__('State')}</th>
                        <th>{__('Type')}</th>
                        <th>{__('Validity')}</th>
                    </tr>
                    </thead>
                    {foreach $licenses->getActive() as $license}
                        <tr>
                            <td>{$license->getID()}</td>
                            <td>{$license->getName()}</td>
                            <td>
                                {include file='tpl_inc/licenses_referenced_item.tpl' license=$license}
                            </td>
                            <td>{__($license->getLicense()->getType())}</td>
                            <td>
                                {include file='tpl_inc/licenses_license.tpl' license=$license}
                            </td>
                        </tr>
                    {/foreach}
                </table>
            </div>
            <div class="save-wrapper">
                {form}
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button class="btn btn-primary btn-block" id="install-all" name="action" value="install-all">
                                <i class="fa fa-share"></i> {__('Install all')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button class="btn btn-primary btn-block" id="update-all" name="action" value="update-all">
                                <i class="fas fa-refresh"></i> {__('Update all')}
                            </button>
                        </div>
                    </div>
                {/form}
            </div>
        {else}
            <p class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noData')}</p>
        {/if}
        </div>
    </div>
</div>
