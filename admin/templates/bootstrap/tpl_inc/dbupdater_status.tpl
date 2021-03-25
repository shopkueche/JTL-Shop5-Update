{function migration_list manager=null title='' filter=0 plugin=null} {* filter: 0 - All, 1 - Executed, 2 - Pending *}
    <div class="card">
        <div class="card-body">
            {if $title|strlen > 0}
                <h4>{$title}</h4>
                <hr class="mb-5">
            {/if}

            <div class="table-responsive">
                <table class="table table-striped table-align-top">
                    <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="60%">{__('migration')}</th>
                        <th width="25%" class="text-center">{if $filter !== 2}{__('executed')}{/if}</th>
                        <th width="10%" class="text-center"></th>
                    </tr>
                    </thead>
                    <tbody>
                      {$migrationIndex = 1}
                      {$executedMigrations = $manager->getExecutedMigrations()}
                      {foreach $manager->getMigrations()|@array_reverse as $m}
                          {$executed = $m->getId()|in_array:$executedMigrations}
                          {if $filter === 0 || ($filter === 1 && $executed) || ($filter === 2 && !$executed)}
                              <tr>
                                  <td>{$migrationIndex++}</td>
                                  <td>
                                    {$m->getDescription()}<br>
                                    {if $m->getCreated()}
                                      <small class="text-muted"><i class="fa fa-clock-o" aria-hidden="true"></i> {$m->getCreated()|date_format:'d.m.Y - H:i:s'}&nbsp;&nbsp;</small>
                                    {/if}
                                    <small class="text-muted"><i class="fa fa-file-code-o" aria-hidden="true"></i> {$m->getName()}</small>
                                  </td>
                                  <td class="text-center"><span class="migration-created">{if $executed}<i class="fal fa-check text-success" aria-hidden="true"></i> {/if}{if $m->getExecuted()}{$m->getExecuted()|date_format:"d.m.Y - H:i:s"}{/if}</span></td>
                                  <td class="text-center">
                                      <a {if $executed}style="display:none"{/if} href="{$url}?action=migration"
                                         data-callback="migration"
                                         data-dir="up"
                                         data-id="{$m->getId()}"
                                         data-plugin="{if $plugin !== null}{$plugin}{else}null{/if}"
                                         class="btn btn-success btn-sm" {if $executed}disabled="disabled"{/if}>
                                          <i class="fa fa-arrow-up"></i>
                                      </a>
                                      <a {if !$executed}style="display:none"{/if}
                                         href="{$url}?action=migration"
                                         data-callback="migration"
                                         data-dir="down"
                                         data-id="{$m->getId()}"
                                         data-plugin="{if $plugin !== null}{$plugin}{else}null{/if}"
                                         class="btn btn-warning btn-sm" {if !$executed}disabled="disabled"{/if}>
                                          <i class="fa fa-arrow-down"></i>
                                      </a>
                                  </td>
                              </tr>
                          {/if}
                      {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
{/function}

{assign var=migrationURL value=$migrationURL|default:'dbupdater.php'}
{assign var=pluginID value=$pluginID|default:null}
{if $pluginID === null}
    <form name="updateForm" method="post" id="form-update">
        {$jtl_token}
        <input type="hidden" name="update" value="1" />
        {if $updatesAvailable}
            <div class="alert alert-warning">
                <h4><i class="fal fa-exclamation-triangle"></i> {__('dbUpdate')} {if $hasDifferentVersions}{__('fromVersion')} {$currentDatabaseVersion} {__('toVersion')} {$currentFileVersion}{/if} {__('required')}.</h4>
                {__('infoUpdateNow')}
            </div>
            <div id="btn-update-group" class="row">
                <div class="col-sm-6 col-xl-auto mb-3">
                    <a href="dbupdater.php?action=update" class="btn btn-success btn-block" data-callback="update"><i class="fa fa-flash"></i> {__('buttonUpdateNow')}</a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <button id="backup-button" type="button" class="btn btn-outline-primary btn-block dropdown-toggle ladda-button" data-size="l" data-style="zoom-out" data-spinner-color="#000" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="ladda-label m-auto">{__('saveCopy')} &nbsp; <i class="fa fa-caret-down"></i></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li class="ml-3"><a href="{$migrationURL}?action=backup" data-callback="backup"><i class="fa fa-cloud-download"></i> &nbsp; {__('putOnServer')}</a></li>
                        <li class="ml-3"><a href="{$migrationURL}?action=backup&download" data-callback="backup" data-download="true"><i class="fa fa-download"></i> &nbsp;{__('download')}</a></li>
                    </ul>
                </div>
            </div>
        {else}
            <div class="alert alert-success h4">
                <p class="text-center">
                    {{__('dbUpToDate')}|sprintf:{$currentDatabaseVersion}}
                </p>
            </div>
        {/if}
    </form>
{/if}
{if isset($manager) && is_object($manager)}
    {migration_list manager=$manager filter=2 title=__('openMigrations') url=$migrationURL plugin=$pluginID}
    {migration_list manager=$manager filter=1 title=__('successfullMigrations') url=$migrationURL plugin=$pluginID}
{/if}
