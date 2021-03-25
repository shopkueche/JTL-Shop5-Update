{include file='tpl_inc/header.tpl'}
{if $inserted !== 0}
    <div class="alert alert-info">{__('msgCreated')}</div>
{/if}
{if $deleted > 0}
    <div class="alert alert-info">{__('msgDeleted')}</div>
{/if}
{if $updated > 0}
    <div class="alert alert-info">{__('msgUpdated')}</div>
{/if}
<div class="tabs">
    <nav class="tabs-nav">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {if $tab === 'overview'} active{/if}" data-toggle="tab" role="tab" href="#overview">
                    {__('queueEntries')}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $tab === 'add-cron'} active{/if}" data-toggle="tab" role="tab" href="#add-cron">
                    {__('createQueueEntry')}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $tab === 'settings'} active{/if}" data-toggle="tab" role="tab" href="#config">
                    {__('settings')}
                </a>
            </li>
        </ul>
    </nav>
    <div class="tab-content">
        <div id="overview" class="settings tab-pane fade{if $tab === 'overview'} active show{/if}">
            {if $jobs|count > 0}
                <div>
                    <div class="subheading1">{__('queueEntries')}</div>
                    <hr class="mb-3">
                    <div class="table-responsive">
                        <form method="post">
                            {$jtl_token}
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{__('headingType')}</th>
                                        <th class="text-center">{__('headingStartTime')}</th>
                                        <th class="text-center">{__('headingLastStarted')}</th>
                                        <th class="text-center">{__('headingFrequency')}</th>
                                        <th class="text-center">{__('headingRunning')}</th>
                                        <th class="text-center">{__('action')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $jobs as $job}
                                        <tr>
                                            <td>{__($job->getType())}{if $job->getName() !== null} {$job->getName()}{/if}</td>
                                            <td class="text-center">{$job->getStartTime()->format('H:i')}</td>
                                            <td class="text-center">{if $job->getDateLastStarted() === null}&dash;{else}{$job->getDateLastStarted()->format('d.m.Y H:i')}{/if}</td>
                                            <td class="text-center">{$job->getFrequency()}h</td>
                                            <td class="text-center">{if $job->isRunning()}<i class="fal fa-check text-success"></i>{else}<i class="fal fa-times text-danger"></i>{/if}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    {*{if $job->isRunning()}*}
                                                        <button class="btn btn-link px-2" type="submit" name="reset" value="{$job->getQueueID()}" title="{__('reset')}" data-toggle="tooltip">
                                                            <span class="icon-hover">
                                                                <span class="fal fa-refresh"></span>
                                                                <span class="fas fa-refresh"></span>
                                                            </span>
                                                        </button>
                                                    {*{/if}*}
                                                    {if $job->getType() !== \JTL\Cron\Type::LICENSE_CHECK}
                                                        <button class="btn btn-link px-2" type="submit" name="delete" value="{$job->getCronID()}" title="{__('delete')}" data-toggle="tooltip">
                                                            <span class="icon-hover">
                                                                <span class="fal fa-trash-alt"></span>
                                                                <span class="fas fa-trash-alt"></span>
                                                            </span>
                                                        </button>
                                                    {else}
                                                        <span class="btn btn-link px-2">
                                                            <span class="icon-hover"></span>
                                                        </span>
                                                    {/if}
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>

        <div id="add-cron" class="settings tab-pane fade{if $tab === 'add-cron'} active show{/if}">
            <div>
                <div class="subheading1">{__('createQueueEntry')}</div>
                <hr class="mb-3">
                <div>
                    <form method="post">
                        {$jtl_token}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cron-type">{__('headingType')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="type" class="custom-select" id="cron-type" required>
                                    {foreach $available as $type}
                                        <option value="{$type}">{__($type)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cron-freq">{__('headingFrequency')} ({__('hours')}):</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <div class="input-group form-counter">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                            <span class="fas fa-minus"></span>
                                        </button>
                                    </div>
                                    <input id="cron-freq" type="number" min="1" value="24" name="frequency" class="form-control" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                            <span class="fas fa-plus"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cron-start">{__('headingStartTime')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input id="cron-start" type="time" name="time" value="00:00" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cron-start-date">{__('headingStartDate')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input id="cron-start-date" type="date" name="date" class="form-control" value="{$smarty.now|date_format:'%Y-%m-%d'}" required>
                            </div>
                        </div>
                        <div class="save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button type="submit" class="btn btn-primary btn-block" name="add-cron" value="1">
                                        <i class="fal fa-save"></i> {__('create')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="config" class="settings tab-pane fade{if $tab === 'settings'} active show{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings'
            action='cron.php' buttonCaption=__('saveWithIcon') tab='einstellungen' title=__('settings')}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}
