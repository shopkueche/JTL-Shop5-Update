{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('wawisync') cBeschreibung=__('wawisyncDesc') cDokuURL=__('wawisyncURL')}
<div id="content">
    <form action="wawisync.php" method="post">
        {$jtl_token}
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('username')}/{__('password')} {__('change')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="wawi-user">{__('user')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input id="wawi-user" name="wawi-user" class="form-control" type="text" value="{$wawiuser}" />
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="wawi-pass">{__('password')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input id="wawi-pass" name="wawi-pass" class="form-control" type="password" value="{$wawipass}" />
                    </div>
                </div>
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="submit" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
