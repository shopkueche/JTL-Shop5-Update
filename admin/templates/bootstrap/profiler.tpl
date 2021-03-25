{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('pluginprofiler') cBeschreibung=__('pluginprofilerDesc') cDokuURL=__('pluginprofilerURL')}
<script type="text/javascript" src="{$templateBaseURL}js/profiler.js"></script>
<script type="text/javascript">
var pies = [];
{foreach $pluginProfilerData as $pie}
    pies.push({ldelim}categories: {$pie->pieChart->categories}, data: {$pie->pieChart->data}, target: 'profile-pie-chart{$pie@index}'{rdelim});
{/foreach}
</script>

<div id="content">
    <div class="card">
        <div class="card-body">
            <form class="delete-run" action="profiler.php" method="post">
                {$jtl_token}
                <input type="hidden" value="y" name="delete-all" />
                <button type="submit" class="btn btn-danger" name="delete-run-submit"><i class="fas fa-trash-alt"></i> {__('deleteAll')}</button>
            </form>
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if !isset($tab) || $tab === 'plugin' || $tab === 'uebersicht'} active{/if}" data-toggle="tab" role="tab" href="#plugins">
                        {__('plugins')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($tab) && $tab === 'sqlprofiler'} active{/if}" data-toggle="tab" role="tab" href="#sqlprofiler">
                        {__('sql')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="plugins" class="tab-pane fade {if !isset($tab) || $tab === 'massaction' || $tab === 'uebersicht'} active show{/if}">
                {if $pluginProfilerData|@count > 0}
                    <div class="card-group" id="accordion" role="tablist" aria-multiselectable="true">
                        {foreach $pluginProfilerData as $profile}
                        <div class="card">
                            <div class="card-header" role="tab" data-idx="{$profile@index}" id="heading-profile-{$profile@index}">
                                <div class="subheading1">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#profile-{$profile@index}" aria-expanded="true" aria-controls="profile-{$profile@index}">
                                        <span class="badge left">{$profile->runID}</span> {$profile->url} - {$profile->timestamp} - {$profile->total_time}s
                                    </a>
                                    <hr class="mb-n3">
                                </div>
                            </div>
                            <div id="profile-{$profile@index}" class="collapse collapse" role="tabpanel" aria-labelledby="heading-profile-{$profile@index}">
                                <div class="card-body">
                                    <div id="profile-pie-chart{$profile@index}" class="profiler-pie-chart"></div>
                                    <div class="list-group">
                                        {foreach $profile->data as $file}
                                            <div class="list-group-item">
                                                <h5 class="list-group-item-heading">{$file->filename|replace:$smarty.const.PFAD_ROOT:''}</h5>
                                                <p class="list-group-item-text">
                                                    {__('hook')}: {$file->hookID}<br />{__('time')}: {$file->runtime}s<br />{__('calls')}: {$file->runcount}
                                                </p>
                                            </div>
                                        {/foreach}
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <form class="delete-run" action="profiler.php" method="post">
                                        {$jtl_token}
                                        <input type="hidden" value="{$profile->runID}" name="run-id" />
                                        <div class="row">
                                            <div class="ml-auto col-sm-6 col-xl-auto">
                                                <button type="submit" class="btn btn-danger btn-block" name="delete-run-submit">
                                                    <i class="fas fa-trash-alt"></i> {__('deleteEntry')}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        {/foreach}
                    </div>
                {else}
                    <div class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="sqlprofiler" class="tab-pane fade{if isset($tab) && $tab === 'sqlprofiler'} active show{/if}">
                {if $sqlProfilerData !== null && $sqlProfilerData|@count > 0}
                    <div class="card-group" id="accordion2" role="tablist" aria-multiselectable="true">
                        {foreach $sqlProfilerData as $run}
                            <div class="card">
                                <div class="card-header" role="tab" data-idx="{$run@index}" id="heading-sql-profile-{$run@index}">
                                    <div class="subheading1">
                                        <a data-toggle="collapse" data-parent="#accordion2" href="#sql-profile-{$run@index}" aria-expanded="true" aria-controls="profile-{$run@index}">
                                            <span class="badge left">{$run->runID}</span> {$run->url} - {$run->timestamp} - {$run->total_time}s
                                        </a>
                                        <hr class="mb-n3">
                                    </div>
                                </div>
                                <div id="sql-profile-{$run@index}" class="collapse collapse" role="tabpanel" aria-labelledby="heading-sql-profile-{$run@index}">
                                    <div class="card-body">
                                        <p><span class="label2">{__('totalQueries')}: </span> <span class="text"> {$run->total_count}</span></p>
                                        <p><span class="label2">{__('runtime')}: </span> <span class="text"> {$run->total_time}</span></p>
                                        <p><span class="label2">{__('tables')}:</span></p>
                                        <ul class="affacted-tables">
                                            {foreach $run->data as $query}
                                                <li class="list a-table">
                                                    <strong>{$query->tablename}</strong> ({$query->runcount} times, {$query->runtime}s)<br />
                                                    {if $query->statement !== null}
                                                        <strong>{__('statement')}:</strong> <code class="sql">{$query->statement}</code><br />
                                                    {/if}
                                                    {if $query->data !== null}
                                                        {assign var=data value=$query->data|@unserialize}
                                                        <strong>{__('backtrace')}:</strong>
                                                        <ol class="backtrace">
                                                            {foreach $data.backtrace as $backtrace}
                                                                <li class="list bt-item">{$backtrace.file}:{$backtrace.line} - {if $backtrace.class !== ''}{$backtrace.class}::{/if}{$backtrace.function}()</li>
                                                            {/foreach}
                                                        </ol>
                                                        {if isset($data.message)}
                                                            <strong>{__('errorMessage')}:</strong>
                                                            {$data.message}
                                                        {/if}
                                                    {/if}
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                    <div class="card-footer save-wrapper">
                                        <form class="delete-run" action="profiler.php" method="post">
                                            {$jtl_token}
                                            <input type="hidden" value="{$run->runID}" name="run-id" />
                                            <div class="row">
                                                <div class="ml-auto col-sm-6 col-xl-auto">
                                                    <button type="submit" class="btn btn-danger btn-block" name="delete-run-submit">
                                                        {__('deleteEntry')}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                {else}
                    <div class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noDataAvailable')}</div>
                {/if}
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
