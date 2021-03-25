{function sprache_buttons}
    <div class="row">
        <div class="ml-auto col-sm-6 col-xl-auto">
            <a class="btn btn-outline-primary btn-block" href="sprache.php?token={$smarty.session.jtl_token}&action=newvar">
                <i class="fa fa-share"></i>
                {__('btnAddVar')}
            </a>
        </div>
        {if $oWert_arr|@count > 0}
            <div class="col-sm-6 col-xl-auto">
                <button type="submit" class="btn btn-primary btn-block" name="action" value="saveall">
                    {__('saveWithIcon')}
                </button>
            </div>
        {/if}
    </div>
{/function}
{include file='tpl_inc/seite_header.tpl' cTitel=__('lang') cBeschreibung=__('langDesc') cDokuURL=__('langURL')}
{assign var=cSearchString value=$oFilter->getField(1)->getValue()}
{assign var=bAllSections value=((int)$oFilter->getField(0)->getValue() === 0)}
<script>
    function toggleTextarea(kSektion, cWertName)
    {
        $('#cWert_' + kSektion + '_' + cWertName).show();
        $('#cWert_caption_' + kSektion + '_' + cWertName).hide();
        $('#bChanged_' + kSektion + '_' + cWertName).val('1');
    }
    function resetVarText(kSektion, cWertName, cStandard)
    {
        $('#cWert_' + kSektion + '_' + cWertName).val($('#cStandard_' + kSektion + '_' + cWertName).text());
        toggleTextarea(kSektion, cWertName);
    }
</script>
<div id="content">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6 col-xl-auto mb-3">
                    {include file='tpl_inc/language_switcher.tpl' id='kSprache' action='sprache.php'}
                </div>
                {if $oWert_arr|@count > 0}
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        {include file='tpl_inc/csv_export_btn.tpl' exporterId="langvars"}
                    </div>
                {/if}
                <div class="col-sm-6 col-xl-auto">
                    {include file='tpl_inc/csv_import_btn.tpl' importerId="langvars" bCustomStrategy=true}
                </div>
            </div>
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav" role="tablist">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link {if $tab === 'variables'}active{/if}" data-toggle="tab" href="#variables">{__('langVars')}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $tab === 'notfound'}active{/if}" data-toggle="tab" href="#notfound">{__('notFoundVars')}</a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="variables" class="tab-pane fade {if $tab === 'variables'}active show{/if}">
                {if $bSpracheAktiv}
                    {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter}
                    <hr>
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination}
                {/if}
                <div>
                    <form action="sprache.php" method="post">
                        {$jtl_token}
                        {if $oWert_arr|@count > 0}
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            {if $bAllSections}<th>{__('section')}</th>{/if}
                                            <th>{__('variableName')}</th>
                                            <th>{__('content')}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $oWert_arr as $oWert}
                                            <tr>
                                                {if $bAllSections}<td>{$oWert->cSektionName}</td>{/if}
                                                <td onclick="toggleTextarea({$oWert->kSprachsektion}, '{$oWert->cName}');"
                                                    style="cursor:pointer;">
                                                    <label for="cWert_{$oWert->kSprachsektion}_{$oWert->cName}">
                                                        {if $cSearchString !== ''}
                                                            {$oWert->cName|regex_replace:"/($cSearchString)/i":"<mark>\$1</mark>"}
                                                        {else}
                                                            {$oWert->cName}
                                                        {/if}
                                                    </label>
                                                </td>
                                                <td onclick="toggleTextarea({$oWert->kSprachsektion}, '{$oWert->cName}');"
                                                    style="cursor:pointer;">
                                                    <span id="cWert_caption_{$oWert->kSprachsektion}_{$oWert->cName}">
                                                        {if $cSearchString !== ''}
                                                            {$oWert->cWert|escape|regex_replace:"/($cSearchString)/i":"<mark>\$1</mark>"}
                                                        {else}
                                                            {$oWert->cWert|escape}
                                                        {/if}
                                                    </span>
                                                    <textarea id="cWert_{$oWert->kSprachsektion}_{$oWert->cName}" class="form-control"
                                                              name="cWert_arr[{$oWert->kSprachsektion}][{$oWert->cName}]"
                                                              style="display:none;">{$oWert->cWert|escape}</textarea>
                                                    <input type="hidden" id="bChanged_{$oWert->kSprachsektion}_{$oWert->cName}"
                                                           name="bChanged_arr[{$oWert->kSprachsektion}][{$oWert->cName}]"
                                                           value="0">
                                                    <span style="display:none;"
                                                          id="cStandard_{$oWert->kSprachsektion}_{$oWert->cName}">{$oWert->cStandard|escape}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        {if $oWert->bSystem === '0'}
                                                            <a href="sprache.php?token={$smarty.session.jtl_token}&action=delvar&kSprachsektion={$oWert->kSprachsektion}&cName={$oWert->cName}"
                                                               class="btn btn-link px-2"
                                                               title="{__('delete')}"
                                                               data-toggle="tooltip">
                                                                <span class="icon-hover">
                                                                    <span class="fal fa-trash-alt"></span>
                                                                    <span class="fas fa-trash-alt"></span>
                                                                </span>
                                                            </a>
                                                        {/if}
                                                        <button type="button"
                                                                class="btn btn-link px-2"
                                                                onclick="resetVarText({$oWert->kSprachsektion}, '{$oWert->cName}');"
                                                                data-toggle="tooltip"
                                                                title="{__('reset')}">
                                                            <span class="icon-hover">
                                                                <span class="fal fa-refresh"></span>
                                                                <span class="fas fa-refresh"></span>
                                                            </span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {elseif $bSpracheAktiv}
                            <div class="alert alert-info" role="alert">{__('noFilterResults')}</div>
                        {else}
                            <div class="alert alert-info" role="alert">{__('notImportedYet')}</div>
                        {/if}
                        <div class="save-wrapper">
                            {sprache_buttons}
                        </div>
                    </form>
                </div>
            </div>
            <div id="notfound" class="tab-pane fade {if $tab === 'notfound'}active show{/if}">
                <div class="table-responsive">
                    {if $oNotFound_arr|@count > 0}
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{__('section')}</th>
                                    <th>{__('variableName')}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $oNotFound_arr as $oWert}
                                    <tr>
                                        <td>{$oWert->cSektion}</td>
                                        <td>{$oWert->cName}</td>
                                        <td>
                                            <div class="btn-group right">
                                                <a href="sprache.php?token={$smarty.session.jtl_token}&action=newvar&kSprachsektion={$oWert->kSprachsektion}&cName={$oWert->cName}&tab=notfound"
                                                   class="btn btn-link px-2"
                                                   title="{__('create')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-plus"></span>
                                                        <span class="fas fa-plus"></span>
                                                    </span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {/if}
                </div>
                <div class="save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a href="sprache.php?token={$smarty.session.jtl_token}&action=clearlog&tab=notfound" class="btn btn-danger btn-block">
                                <i class="fa fa-refresh"></i>
                                {__('btnResetLog')}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
