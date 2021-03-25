{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('redirect') cBeschreibung=__('redirectDesc') cDokuURL=__('redirectURL')}
{include file='tpl_inc/sortcontrols.tpl'}

{assign var=cTab value=$cTab|default:'redirects'}

<script>
    $(function () {
        {foreach $oRedirect_arr as $oRedirect}
            var $stateChecking    = $('#input-group-{$oRedirect->kRedirect} .state-checking');
            var $stateAvailable   = $('#input-group-{$oRedirect->kRedirect} .state-available');
            var $stateUnavailable = $('#input-group-{$oRedirect->kRedirect} .state-unavailable');

            {if $oRedirect->cAvailable === 'y'}
                $stateChecking.hide();
                $stateAvailable.show();
            {elseif $oRedirect->cAvailable === 'n'}
                $stateChecking.hide();
                $stateUnavailable.show();
            {else}
                checkUrl({$oRedirect->kRedirect}, true);
            {/if}
        {/foreach}
    });

    function checkUrl(kRedirect, doUpdate)
    {
        doUpdate = doUpdate || false;

        var $stateChecking    = $('#input-group-' + kRedirect + ' .state-checking');
        var $stateAvailable   = $('#input-group-' + kRedirect + ' .state-available');
        var $stateUnavailable = $('#input-group-' + kRedirect + ' .state-unavailable');

        $stateChecking.show();
        $stateAvailable.hide();
        $stateUnavailable.hide();

        function checkUrlCallback(result)
        {
            $stateChecking.hide();
            $stateAvailable.hide();
            $stateUnavailable.hide();

            if (result === true) {
                $stateAvailable.show();
            } else {
                $stateUnavailable.show();
            }
        }

        if(doUpdate) {
            ioCall('updateRedirectState', [kRedirect], checkUrlCallback);
        } else {
            ioCall('redirectCheckAvailability', [$('#cToUrl-' + kRedirect).val()], checkUrlCallback);
        }
    }

    function redirectTypeahedDisplay(item)
    {
        return '/' + item.cSeo;
    }

    function redirectTypeahedSuggestion(item)
    {
        var type = '';
        switch(item.cKey) {
            case 'kLink': type = 'Seite'; break;
            case 'kNews': type = 'News'; break;
            case 'kNewsKategorie': type = 'News-Kategorie'; break;
            case 'kNewsMonatsUebersicht': type = 'News-Montasübersicht'; break;
            case 'kArtikel': type = 'Artikel'; break;
            case 'kKategorie': type = 'Kategorie'; break;
            case 'kHersteller': type = 'Hersteller'; break;
            case 'kMerkmalWert': type = 'Merkmal-Wert'; break;
            case 'suchspecial': type = 'Suchspecial'; break;
            default: type = 'Anderes'; break;
        }
        return '<span>/' + item.cSeo +
            ' <small class="text-muted">- ' + type + '</small></span>';
    }

    function toggleReferer(kRedirect)
    {
        var $refTr  = $('#referer-tr-' + kRedirect);
        var $refDiv = $('#referer-div-' + kRedirect);

        if(!$refTr.is(':visible')) {
            $refTr.show();
            $refDiv.slideDown();
        } else {
            $refDiv.slideUp(500, $refTr.hide.bind($refTr));
        }
    }
</script>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6 col-xl-auto">
                {include file='tpl_inc/csv_export_btn.tpl' exporterId='redirects'}
            </div>
            <div class="{if !$oRedirect_arr|@count > 0}ml-auto{/if} col-sm-6 col-xl-auto">
                {include file='tpl_inc/csv_import_btn.tpl' importerId='redirects'}
            </div>
        </div>
    </div>
</div>

<div class="tabs">
    <nav class="tabs-nav">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {if $cTab === 'redirects'} active{/if}" data-toggle="tab" role="tab" href="#redirects">
                    {__('overview')}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {if $cTab === 'new_redirect'} active{/if}" data-toggle="tab" role="tab" href="#new_redirect">
                    {__('create')}
                </a>
            </li>
        </ul>
    </nav>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade{if $cTab === 'redirects'} active show{/if}" id="redirects">
            {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter}
            {include file='tpl_inc/pagination.tpl' pagination=$pagination cAnchor='redirects'}
            <div>
                <form method="post">
                    {$jtl_token}
                    {if $oRedirect_arr|@count > 0}
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>{__('redirectFrom')} {call sortControls pagination=$pagination nSortBy=0}</th>
                                        <th class="min-w">{__('redirectTo')} {call sortControls pagination=$pagination nSortBy=1}</th>
                                        <th class="text-center">{__('redirectRefererCount')} {call sortControls pagination=$pagination nSortBy=2}</th>
                                        <th class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $oRedirect_arr as $oRedirect}
                                        <tr>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" name="redirects[{$oRedirect->kRedirect}][enabled]" value="1"
                                                       id="check-{$oRedirect->kRedirect}">
                                                    <label class="custom-control-label" for="check-{$oRedirect->kRedirect}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <label for="check-{$oRedirect->kRedirect}">
                                                    <a href="{$oRedirect->cFromUrl}" target="_blank"
                                                       {if $oRedirect->cFromUrl|strlen > 50}data-toggle="tooltip"
                                                       data-placement="bottom" title="{$oRedirect->cFromUrl}"{/if}>
                                                        {$oRedirect->cFromUrl|truncate:50}
                                                    </a>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="form-group form-row align-items-center" id="input-group-{$oRedirect->kRedirect}">
                                                    <span class="col col-lg-auto col-form-label text-info state-checking">
                                                        <i class="fa fa-spinner fa-pulse"></i>
                                                    </span>
                                                    <span class="col col-lg-auto col-form-label text-success state-available" style="display:none;">
                                                        <i class="fal fa-check"></i>
                                                    </span>
                                                    <span class="col col-lg-auto col-form-label text-danger state-unavailable" style="display:none;">
                                                        <i class="fal fa-exclamation-triangle"></i>
                                                    </span>
                                                    <div class="col col-md-10">
                                                        <input class="form-control min-w-sm" name="redirects[{$oRedirect->kRedirect}][cToUrl]"
                                                               value="{$oRedirect->cToUrl}" id="cToUrl-{$oRedirect->kRedirect}"
                                                               onblur="checkUrl({$oRedirect->kRedirect})">
                                                    </div>
                                                    <script>
                                                        enableTypeahead(
                                                            '#cToUrl-{$oRedirect->kRedirect}', 'getSeos',
                                                            redirectTypeahedDisplay, redirectTypeahedSuggestion,
                                                            checkUrl.bind(null, {$oRedirect->kRedirect}, false)
                                                        );
                                                    </script>

                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {if $oRedirect->nCount > 0}
                                                    <span class="badge badge-primary font-weight-bold font-size-sm">{$oRedirect->nCount}</span>
                                                {/if}
                                            </td>
                                            <td class="text-center">
                                                {if $oRedirect->nCount > 0}
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-link px-2" title="{__('details')}"
                                                                onclick="toggleReferer({$oRedirect->kRedirect});"
                                                                data-toggle="tooltip">
                                                            <span class="fal fa-chevron-circle-down rotate-180 font-size-lg"></span>
                                                        </button>
                                                    </div>
                                                {/if}
                                            </td>
                                        </tr>
                                        {if $oRedirect->nCount > 0}
                                            <tr id="referer-tr-{$oRedirect->kRedirect}" style="display:none;">
                                                <td></td>
                                                <td colspan="5">
                                                    <div id="referer-div-{$oRedirect->kRedirect}" style="display:none;">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>{__('redirectReferer')}</th>
                                                                    <th>{__('date')}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {foreach $oRedirect->oRedirectReferer_arr as $oRedirectReferer}
                                                                    <tr>
                                                                        <td>
                                                                            {if $oRedirectReferer->kBesucherBot > 0}
                                                                                {if $oRedirectReferer->cBesucherBotName|strlen > 0}
                                                                                    {$oRedirectReferer->cBesucherBotName}
                                                                                {else}
                                                                                    {$oRedirectReferer->cBesucherBotAgent}
                                                                                {/if}
                                                                                (Bot)
                                                                            {elseif $oRedirectReferer->cRefererUrl|strlen > 0}
                                                                                <a href="{$oRedirectReferer->cRefererUrl}" target="_blank">
                                                                                    {$oRedirectReferer->cRefererUrl}
                                                                                </a>
                                                                            {else}
                                                                                <i>{__('redirectRefererDirect')}</i>
                                                                            {/if}
                                                                        </td>
                                                                        <td>
                                                                            {$oRedirectReferer->dDate|date_format:'%d.%m.%Y - %H:%M:%S'}
                                                                        </td>
                                                                    </tr>
                                                                {/foreach}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {elseif $nTotalRedirectCount > 0}
                        <div class="alert alert-info" role="alert">{__('noFilterResults')}</div>
                    {else}
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {/if}
                    <div class="save-wrapper">
                        <div class="row">
                            <div class="col-sm-6 col-xl-auto text-left">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="ALLMSGS" id="ALLMSGS" onclick="AllMessages(this.form);">
                                    <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                                </div>
                            </div>
                            {if $oRedirect_arr|@count > 0}
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button name="action" value="delete" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                    </button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button name="action" value="delete_all" class="btn btn-warning btn-block">
                                        {__('redirectDelUnassigned')}
                                    </button>
                                </div>
                            {/if}
                            <div class="ol-sm-6 col-xl-auto">
                                <button name="action" value="save" class="btn btn-primary btn-block">
                                    {__('saveWithIcon')}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane fade{if $cTab === 'new_redirect'} active show{/if}" id="new_redirect">
            <form method="post">
                {$jtl_token}
                <div class="settings">
                    <div class="subheading1">{__('redirectNew')}</div>
                    <hr class="mb-3">
                    <div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cFromUrl">{__('redirectFrom')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" id="cFromUrl" name="cFromUrl" required
                                       {if !empty($cFromUrl)}value="{$cFromUrl}"{/if}>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center" id="input-group-0">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cToUrl-0">{__('redirectTo')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" id="cToUrl-0" name="cToUrl" required
                                       onblur="checkUrl(0)" {if !empty($cToUrl)}value="{$cToUrl}"{/if}>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3" style="display:none;">
                                <i class="fa fa-spinner fa-pulse text-info state-checking"></i>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3" style="display:none;">
                                <i class="fal fa-check text-success text-success state-available"></i>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                <i class="fal fa-exclamation-triangle text-danger state-unavailable"></i>
                            </div>
                            <script>
                                enableTypeahead(
                                    '#cToUrl-0', 'getSeos', redirectTypeahedDisplay, redirectTypeahedSuggestion,
                                    checkUrl.bind(null, 0, false)
                                )
                            </script>
                        </div>
                    </div>
                    <div class="save-wrapper">
                        <div class="row">
                            <div class="ml-auto col-sm-6 col-xl-auto">
                                <button name="action" value="new" class="btn btn-primary btn-block">
                                    <i class="fa fa-save"></i> {__('create')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
