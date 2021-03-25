{if empty($tab)}
    {if $engineUpdate->tableCount > 10}
        {assign var=tab value='update_automatic'}
    {else}
        {assign var=tab value='update_individual'}
    {/if}
{/if}
<div class="alert alert-warning">
    <div class="card-title">{__('structureMigrationNeeded')}</div>
    {{__('structureMigrationNeededLong')}|sprintf:{$engineUpdate->tableCount}:{$engineUpdate->dataSize|formatByteSize:'%.0f'|upper|strip:'&nbsp;'}}
</div>
{if $DB_Version->collation_utf8 && $DB_Version->innodb->support}
    {if $DB_Version->innodb->support && $DB_Version->innodb->version|version_compare:'5.6' < 0}
        <div class="alert alert-warning">
            <div class="card-title">{__('warningOldDBVersion')}</div>
            {{__('warningOldDBVersionLong')}|sprintf:{$DB_Version->server}}
            {if (isset($Einstellungen.artikeluebersicht.suche_fulltext) && $Einstellungen.artikeluebersicht.suche_fulltext !== 'N') || $FulltextIndizes !== false}
                <ul>
                    {if (isset($Einstellungen.artikeluebersicht.suche_fulltext) && $Einstellungen.artikeluebersicht.suche_fulltext !== 'N')}
                    <li>{__('fullTextDeactivate')}</li>
                    {/if}
                    {if $FulltextIndizes !== false}
                    {foreach $FulltextIndizes as $index}
                    <li>{{__('fullTextDelete')}|sprintf:{$index->INDEX_NAME}:{$index->TABLE_NAME}}</li>
                    {/foreach}
                    {/if}
                </ul>
            {/if}
        </div>
    {/if}
    {if $DB_Version->innodb->size !== 'auto' && $engineUpdate->dataSize > $DB_Version->innodb->size}
        <div class="alert alert-warning">
            <div class="card-title">{__('notEnoughTableSpace')}</div>
            {{__('notEnoughTableSpaceLong')}|sprintf:{$DB_Version->innodb->size|formatByteSize:'%.0f'|upper|strip:'&nbsp;'}}
        </div>
    {/if}
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link{if $tab === 'update_individual'} active{/if}" data-toggle="tab" role="tab" href="#update_individual">{__('soloStructureTable')}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{if $tab === 'update_automatic'} active{/if}" data-toggle="tab" role="tab" href="#update_automatic">{__('automatic')}</a>
                </li>
                {if isset($scriptGenerationAvailable) && $scriptGenerationAvailable}
                <li class="nav-item">
                    <a class="nav-link{if $tab === 'update_script'} active{/if}" data-toggle="tab" role="tab" href="#update_script">{__('viaScriptConsole')}</a>
                </li>
                {/if}
            </ul>
        </nav>
        <div class="tab-content">
            <div id="update_individual" class="tab-pane fade{if $tab === 'update_individual'} show active{/if}">
                <h3>{__('soloStructureTable')}</h3>
                <p>{__('noteSoloMigration')}</p>
                <p>{__('noteSoloMigrationClick')}</p>
                <div class="alert alert-warning">{__('warningDoBackupSingle')}</div>
            </div>
            <div id="update_automatic" class="tab-pane fade{if $tab === 'update_automatic'} show active{/if}">
                <h3>{__('automatic')}</h3>
                <p>{__('noteRecommendMigration')}</p>
                {{__('notePatienceOne')}|sprintf:{$engineUpdate->tableCount}:{$engineUpdate->dataSize|formatByteSize:'%.0f'|upper|strip:'&nbsp;'}}
                <p>
                    {if $engineUpdate->estimated[0] < 60}
                        {__('lessThanOneMinute')}
                    {elseif $engineUpdate->estimated[0] < 3600}
                        {__('approximately')} {($engineUpdate->estimated[0] / 60)|round:0} {__('minutes')}
                    {else}
                        {__('approximately')} {($engineUpdate->estimated[0] / 3600)|round:1} {__('hours')}
                    {/if} {__('ifNecessaryUpTo')}
                    {if $engineUpdate->estimated[1] < 60}
                        {__('oneMinute')}
                    {elseif $engineUpdate->estimated[1] < 3600}
                        {__('approximately')} {($engineUpdate->estimated[1] / 60)|ceil} {__('minutes')}
                    {else}
                        {__('approximately')} {($engineUpdate->estimated[1] / 3600)|ceil} {__('hours')}
                    {/if}
                    {{__('notePatienceTwo')}|sprintf:{$shopURL}:{$smarty.const.PFAD_ADMIN}}
                </p>
                <div class="alert alert-warning">{__('warningDoBackup')}</div>
                <form method="post" action="dbcheck.php">
                    <div id="settings" class="card">
                        <div class="card-body">
                            <div class="custom-control custom-checkbox">
                                <input id="update_auto_backup" class="custom-control-input form-control" type="checkbox" name="update_auto_backup" value="1" required>
                                <label class="custom-control-label" for="update_auto_backup">{__('yesBackup')}</label>
                            </div>
                            {if isset($Einstellungen.global.wartungsmodus_aktiviert) && $Einstellungen.global.wartungsmodus_aktiviert === 'Y'}
                            <div class="input-group">
                                <span class="input-group-addon"><span class="badge alert-success">{__('maintenanceActive')}</span></span>
                                <input id="update_auto_wartungsmodus" type="hidden" name="update_auto_wartungsmodus" value="1" >
                            </div>
                            {else}
                            <div class="custom-control custom-checkbox">
                                <input id="update_auto_wartungsmodus_reject" class="custom-control-input form-control" type="checkbox" name="update_auto_wartungsmodus_reject" value="1" required>
                                <label class="custom-control-label" for="update_auto_wartungsmodus_reject">{__('noMaintenance')}</label>
                            </div>
                            {/if}
                            {if $DB_Version->innodb->size !== 'auto' && $engineUpdate->dataSize > $DB_Version->innodb->size}
                            <div class="custom-control custom-checkbox">
                                <input id="update_auto_size_skip" class="custom-control-input form-control" type="checkbox" name="update_auto_size_skip" value="1" required>
                                <label class="custom-control-label" for="update_auto_size_skip">{__('yesEnoughSpace')}</label>
                            </div>
                            {else}
                            <input id="update_auto_size" type="hidden" name="update_auto_size" value="1" >
                            {/if}
                        </div>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-primary" name="update" value="automatic"><i class="fa fa-cogs"></i> {__('buttonMigrationStart')}</button>
                    </div>
                </form>
            </div>
            {if isset($scriptGenerationAvailable) && $scriptGenerationAvailable}
            <div id="update_script" class="tab-pane fade{if $tab === 'update_script'} show active{/if}">
                <h3>{__('viaScriptConsole')}</h3>
                <p>{__('noteMigrationScript')}</p>
                <p>{__('noteMigrationScriptClick')}</p>
                <p>{__('noteMigrationScriptDesc')}</p>
                <p>{{__('noteMigrationScriptMaintenance')}|sprintf:{$shopURL}:{$smarty.const.PFAD_ADMIN}}</p>
                <div class="alert alert-warning">{__('warningDoBackupScript')}</div>
                <div class="alert alert-warning">{__('warningUseConsoleScript')}</div>
                <div class="alert alert-warning">{__('warningUseThisShopScript')}</div>
                <form action="{$adminURL}/dbcheck.php" method="post">
                    {$jtl_token}
                    <div class="btn-group">
                        <button class="btn btn-primary" name="update" value="script"><i class="fa fa-cogs"></i> {__('buttonCreateScript')}</button>
                    </div>
                </form>
            </div>
            {/if}
        </div>
    </div>
    <script>
        {if !empty($tab) && $tab !== 'update_individual'}
        {literal}
        $(document).ready(function () {
            $('#contentCheck').hide();
        });
        {/literal}
        {/if}
        {literal}
        function doAutoMigration(status, table,  step, exclude) {
            if (cancelWait() && window.confirm('{/literal}{__('sureCancelStructureMigration')}{literal}')) {
                updateModalWait('{/literal}{__('cancelMigration')}{literal}', 1);
                window.location.reload(true);
                return;
            } else {
                cancelWait(false);
            }

            if (typeof status === 'undefined' || status === null) {
                status = 'start';
            }
            if (typeof step === 'undefined' || step === null || step === 0) {
                step = 1;
            }
            if (typeof table !== 'undefined' && table !== null && table !== '') {
                updateModalWait('{/literal}{__('migrateOf')}{literal}' + table + ' - {/literal}{__('step')}{literal} ' + step);
            } else {
                table = '';
            }
            if (typeof exclude === 'undefined' && exclude !== null) {
                exclude = [];
            }
            if (status === 'finished') {
                updateModalWait('{/literal}{__('cancelMigration')}{literal}');
                window.location.reload(true);
            } else {
                ioCall('migrateToInnoDB_utf8', [status, table, step, exclude],
                    function (data, context) {
                        if (data && typeof data.status !== 'undefined') {
                            if (data.status === 'migrate') {
                                // migrate next table...
                                if (data.nextTable === table && data.nextStep === 1) {
                                    exclude.push(table);
                                    updateModalWait(null, 1);
                                } else if (data.nextStep === 1) {
                                    updateModalWait(null, 1);
                                }
                                doAutoMigration(data.status, data.nextTable, data.nextStep, exclude);
                            } else if (data.status === 'failure' || data.status === 'in_use') {
                                var msg = data.status === 'failure'
                                    ? sprintf('{/literal}{__('errorMigrationTableContinue')}{literal}', table)
                                    : table + '{/literal}{__('errorTableInUse')}{literal}';
                                if (window.confirm(msg)) {
                                    exclude.push(table);
                                    updateModalWait(null, 1);
                                    doAutoMigration('start', '', 1, exclude);
                                } else {
                                    updateModalWait('{/literal}{__('cancelMigration')}{literal}', 1);
                                    window.location.reload(true);
                                }
                            } else if (data.status === 'all done') {
                                updateModalWait('{/literal}{__('clearCache')}{literal}', 1);
                                doAutoMigration('clear cache', null, null, exclude);
                            } else {
                                // Migration finished
                                updateModalWait('{/literal}{__('cancelMigration')}{literal}', 1);
                                window.location.reload(true);
                            }
                        } else {
                            if (window.confirm(sprintf('{/literal}{__('errorMigrationTableContinue')}{literal}', table))) {
                                exclude.push(table);
                                updateModalWait(null, 1);
                                doAutoMigration('start', '', 1, exclude);
                            } else {
                                updateModalWait('{/literal}{__('cancelMigration')}{literal}', 1);
                                window.location.reload(true);
                            }
                        }
                    },
                    function (responseJSON) {
                        if (window.confirm(sprintf('{/literal}{__('errorMigrationTableContinue')}{literal}', table))) {
                            exclude.push(table);
                            updateModalWait(null, 1);
                            doAutoMigration('start', '', 1, exclude);
                        } else {
                            updateModalWait('{/literal}{__('cancelMigration')}{literal}', 1);
                            window.location.reload(true);
                        }
                    },
                    {}
                )
            }
        }
        $('.nav-tabs a[href="#update_individual"]')
            .on('hidden.bs.tab', function(event){
                $('#contentCheck').hide();
            })
            .on('shown.bs.tab', function(event){
                $('#contentCheck').show();
            });
        $('form', '#update_automatic').on('submit', function (e) {
            if ($('#update_auto_backup').is(':checked')
                && ($('#update_auto_wartungsmodus_reject').is(':checked') || parseInt($('#update_auto_wartungsmodus').val()) === 1)
                && ($('#update_auto_size_skip').is(':checked') || parseInt($('#update_auto_size').val()) === 1)) {
                showModalWait('{/literal}{__('startAutomaticMigration')}{literal}', {/literal}{$engineUpdate->tableCount}{literal} + 1);
                doAutoMigration('start');
            } else {
                alert('{/literal}{__('notApproveMaintenance')}{literal}');
            }

            e.preventDefault();
        });
        {/literal}
    </script>
{else}
    {if !$DB_Version->innodb->support}
    <div class="alert alert-danger">
        <div class="card-title">{__('errorNoInnoDBSupport')}</div>
        {{__('errorNoInnoDBSupportDesc')}|sprintf:{$DB_Version->server}}
    </div>
    {/if}
    {if !$DB_Version->collation_utf8}
        <div class="alert alert-danger">
            <div class="card-title">{__('errorNoUTF8Support')}</div>
            {{__('errorNoUTF8SupportDesc')}|sprintf:{$DB_Version->server}}
        </div>
    {/if}
{/if}
