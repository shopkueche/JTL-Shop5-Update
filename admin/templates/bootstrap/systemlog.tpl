{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('systemlog') cBeschreibung=__('systemlogDesc') cDokuURL=__('systemlogURL')}
{assign var=cTab value=$cTab|default:'log'}
<div class="tabs">
    <nav class="tabs-nav">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="nav-item">
                <a class="nav-link {if $cTab === 'log'} active{/if}" data-toggle="tab" role="tab" href="#log">
                    {__('systemlogLog')}
                </a>
            </li>
            <li role="presentation" class="nav-item">
                <a class="nav-link {if $cTab === 'config'} active{/if}" data-toggle="tab" role="tab" href="#config">
                    {__('systemlogConfig')}
                </a>
            </li>
        </ul>
    </nav>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade{if $cTab === 'log'} active show{/if}" id="log">
            {if $nTotalLogCount !== 0}
                {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter}
                {include file='tpl_inc/pagination.tpl' pagination=$pagination}
            {/if}

            <div>
                <form method="post" action="systemlog.php">
                    {$jtl_token}
                    {if $nTotalLogCount === 0}
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {elseif $oLog_arr|@count === 0}
                        <div class="alert alert-info" role="alert">{__('noFilterResults')}</div>
                    {else}
                        <div class="listgroup">
                            {foreach $oLog_arr as $oLog}
                                <div class="list-group-item border-left-0 border-right-0 {cycle values="bg-light-gray,"}">
                                    <div class="row">
                                        <div class="col-md-3 col-xs-12">
                                            <label class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="checkbox" name="selected[]" id="log-id-{$oLog->kLog}" value="{$oLog->kLog}">
                                                <label class="custom-control-label" for="log-id-{$oLog->kLog}"></label>
                                                {if $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_ERROR}
                                                    <span class="badge badge-danger">{__('systemlogError')}</span>
                                                {elseif $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_WARNING}
                                                    <span class="badge badge-warning">{__('systemlogWarning')}</span>
                                                {elseif $oLog->nLevel > $smarty.const.JTLLOG_LEVEL_DEBUG}
                                                    <span class="badge badge-success">{__('systemlogNotice')}</span>
                                                {else}
                                                    <span class="badge badge-info info">{__('systemlogDebug')}</span>
                                                {/if}
                                                {$oLog->dErstellt|date_format:'d.m.Y - H:i:s'}
                                            </label>
                                        </div>
                                        <div class="col-md-9 col-xs-12">
                                            <pre class="logtext p-1">{$oLog->cLog}</pre>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {/if}
                    <div class="save-wrapper">
                        <div class="row">
                            <div class="col-sm-6 col-xl-auto text-left">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" id="select-all-2" name="aaa" value="bbb"
                                           onchange="selectAllItems(this, $(this).prop('checked'))">
                                    <label class="custom-control-label" for="select-all-2">{__('selectAllShown')}</label>
                                </div>
                            </div>
                            <div class="ml-auto col-sm-6 col-xl-auto">
                                <button name="action" value="delselected" class="btn btn-warning btn-block">
                                    <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                </button>
                            </div>
                            <div class="col-sm-6 col-xl-auto">
                                <button name="action" value="clearsyslog" class="btn btn-danger btn-block">
                                    <i class="fas fa-trash-alt"></i> {__('systemlogReset')}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane fade{if $cTab === 'config'} active show{/if}" id="config">
            <form class="sttings" action="systemlog.php" method="post">
                {$jtl_token}
                <div class="subheading1">{__('systemlogLevel')}</div>
                <hr class="mb-3">
                <div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="minLogLevel">{__('minLogLevel')}:</label>
                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select name="minLogLevel" id="minLogLevel" class="custom-select combo">
                                <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_ERROR} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_ERROR}">{__('logLevelError')}</option>
                                <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_WARNING} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_WARNING}">{__('logLevelWarning')}</option>
                                <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_NOTICE} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_NOTICE}">{__('logLevelNotice')}</option>
                                <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_DEBUG} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_DEBUG}">{__('logLevelDebug')}</option>
                            </select>
                        </span>
                    </div>
                </div>
                <div class="save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="action" value="save" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
