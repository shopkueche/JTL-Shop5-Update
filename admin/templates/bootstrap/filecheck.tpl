{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('filecheck') cBeschreibung=__('filecheckDesc') cDokuURL=__('filecheckURL')}

{$alertList->displayAlertByKey('orphanedFilesError')}
{$alertList->displayAlertByKey('modifiedFilesError')}
{$alertList->displayAlertByKey('backupMessage')}
{$alertList->displayAlertByKey('zipArchiveError')}

<div class="card collapsed">
    <div class="card-header{if $modifiedFiles|count > 0} accordion-toggle" data-toggle="collapse" data-target="#pageCheckModifiedFiles" style="cursor:pointer"{else}"{/if}>
        <div class="card-title">
            {if $modifiedFiles|count > 0}<i class="fa fas fa-plus"></i> {/if}
            {__('fileCheckNumberModifiedFiles')}: {$modifiedFiles|count}
        </div>
    </div>
    {if $modifiedFiles|count > 0}
        <div class="card-body  collapse" id="pageCheckModifiedFiles">
            <p class="small text-muted">{__('fileCheckModifiedFilesNote')}</p>
            <div id="contentModifiedFilesCheck">
                <table class="table table-sm table-borderless req">
                    <thead>
                    <tr>
                        <th class="text-left">{__('file')}</th>
                        <th class="text-right">{__('lastModified')}</th>
                    </tr>
                    </thead>
                    {foreach $modifiedFiles as $file}
                        <tr class="filestate mod{$file@iteration % 2} modified">
                            <td class="text-left">{$file->name}</td>
                            <td class="text-right">{$file->lastModified}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        </div>
    {/if}
</div>
<div class="card collapsed">
    <div class="card-header{if $orphanedFiles|count > 0} accordion-toggle" data-toggle="collapse" data-target="#pageCheckOrphanedFiles" style="cursor:pointer"{else}"{/if}>
        <div class="card-title">
            {if $orphanedFiles|count > 0}<i class="fa fas fa-plus"></i> {/if}
            {__('fileCheckNumberOrphanedFiles')}: {$orphanedFiles|count}
        </div>
    </div>
    {if $orphanedFiles|count > 0}
        <div class="card-body  collapse" id="pageCheckOrphanedFiles">
            <p class="alert alert-info">{__('fileCheckOrphanedFilesNote')}</p>
            <div id="contentOrphanedFilesCheck">
                <table class="table table-sm table-borderless req">
                    <thead>
                        <tr>
                            <th class="text-left">{__('file')}</th>
                            <th class="text-right">{__('lastModified')}</th>
                        </tr>
                    </thead>
                    {foreach $orphanedFiles as $file}
                        <tr class="filestate mod{$file@iteration % 2} orphaned">
                            <td class="text-left">{$file->name}</td>
                            <td class="text-right">{$file->lastModified}</td>
                        </tr>
                    {/foreach}
                </table>
                <div class="save-wrapper">
                    <form method="post">
                        {$jtl_token}
                        <div class="row">
                            <div class="ml-auto col-sm-6 col-xl-auto">
                                <button class="btn btn-danger btn-block" name="delete-orphans" value="1" onclick="return confirmDelete();">
                                    <i class="fa fas fa-trash"></i> {__('delete')}
                                </button>
                            </div>
                            <div class="col-sm-6 col-xl-auto">
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#show-script" aria-expanded="false" aria-controls="show-script">
                                    <i class="fa fas fa-terminal"></i> {__('showDeleteScript')}
                                </button>
                            </div>
                        </div>
                        <div class="collapse" id="show-script">
                            <div class="card card-body">
                                <pre style="margin-top:1em;">{$deleteScript}</pre>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    {/if}
</div>
<script type="text/javascript">
    function confirmDelete() {ldelim}
        return confirm('{__('confirmDeleteText')}');
    {rdelim}
</script>
{include file='tpl_inc/footer.tpl'}
