{include file='tpl_inc/seite_header.tpl' cTitel=__('emailTemplates')}
<div id="content">
    <div class="card">
        <div class="card-body">
            <form method="post" action="emailvorlagen.php">
                {$jtl_token}
                <input type="hidden" name="resetEmailvorlage" value="1" />
                {if $mailTemplate->getPluginID() > 0}
                    <input type="hidden" name="kPlugin" value="{$mailTemplate->getPluginID()}" />
                {/if}
                <input type="hidden" name="kEmailvorlage" value="{$mailTemplate->getID()}" />
                <div class="alert alert-danger">
                    <p><strong>{__('danger')}</strong>: {__('resetEmailTemplate')}</p>

                    <p>{{__('sureResetEmailTemplate')}|sprintf:{__('name_'|cat:$mailTemplate->getModuleID())}}</p>
                </div>
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto mb-2">
                        <button name="resetConfirmJaSubmit" type="submit" value="{__('yes')}" class="btn btn-danger btn-block min-w-sm">
                            <i class="fal fa-check"></i> {__('yes')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button name="resetConfirmNeinSubmit" type="submit" value="{__('no')}" class="btn btn-outline-primary btn-block min-w-sm">
                            <i class="fa fa-close"></i> {__('no')}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
