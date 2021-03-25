<div id="aktiviert" class="tab-pane fade {if $cTab === '' || $cTab === 'aktiviert'} active show{/if}">
    {if $pluginsInstalled->count() > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php" id="enabled-plugins">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div>
                <div class="subheading1">{__('pluginListInstalled')}</div>
                <hr class="mb-3">
                <div class="table-responsive">
                    <table class="table table-striped table-align-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="text-left">{__('pluginName')}</th>
                                <th class="text-center">{__('pluginVersion')}</th>
                                <th class="text-center">{__('pluginCompatibility')}</th>
                                <th class="text-center">{__('pluginInstalled')}</th>
                                <th>{__('pluginFolder')}</th>
                                <th class="text-center">{__('pluginEditLocales')}</th>
                                <th class="text-center">{__('pluginEditLinkgrps')}</th>
                                <th class="text-center">{__('pluginBtnLicence')}</th>
                                <th class="text-center">{__('actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsInstalled as $plugin}
                            <tr{if $plugin->isUpdateAvailable()} class="highlight"{/if}>
                                <td class="check">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" name="kPlugin[]" id="plugin-check-{$plugin->getID()}" value="{$plugin->getID()}" />
                                        <label class="custom-control-label" for="plugin-check-{$plugin->getID()}"></label>
                                    </div>
                                </td>
                                <td>
                                    <label for="plugin-check-{$plugin->getID()}">{$plugin->getName()}</label>
                                    {if $plugin->getMinShopVersion()->greaterThan($shopVersion)}
                                        <span title="{__('dangerMinShopVersion')}" class="label text-danger" data-toggle="tooltip">
                                            <span class="icon-hover">
                                                <span class="fal fa-exclamation-triangle"></span>
                                                <span class="fas fa-exclamation-triangle"></span>
                                            </span>
                                        </span>
                                    {elseif $plugin->getMaxShopVersion()->greaterThan('0.0.0') && $plugin->getMaxShopVersion()->smallerThan($shopVersion)}
                                        <span title="{__('dangerMaxShopVersion')}" class="label text-danger" data-toggle="tooltip">
                                            <span class="icon-hover">
                                                <span class="fal fa-exclamation-triangle"></span>
                                                <span class="fas fa-exclamation-triangle"></span>
                                            </span>
                                        </span>
                                    {/if}
                                </td>
                                <td class="text-center plugin-version">
                                    {(string)$plugin->getVersion()}{if $plugin->isUpdateAvailable()} <span class="badge update-available">{(string)$plugin->isUpdateAvailable()}</span>{/if}
                                    {if $plugin->isShop5Compatible() === false}
                                        <span title="{__('dangerPluginNotCompatibleShop5')}" class="label text-warning"><i class="fal fa-exclamation-triangle"></i></span>
                                    {elseif $plugin->isShop5Compatible() === false && $p->isShop4Compatible() === false}
                                        <span title="{__('dangerPluginNotCompatibleShop4')}" class="label text-warning"><i class="fal fa-exclamation-triangle"></i></span>
                                    {/if}
                                </td>
                                <td class="text-center">{$plugin->displayVersionRange()}</td>
                                <td class="text-center plugin-install-date">{$plugin->getDateInstalled()->format('d.m.Y H:i')}</td>
                                <td class="plugin-folder">{$plugin->getDir()}</td>
                                <td class="text-center plugin-lang-vars">
                                    {if $plugin->getLangVarCount() > 0}
                                        <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}&token={$smarty.session.jtl_token}"
                                           class="btn btn-link"
                                           title="{__('modify')}"
                                           data-toggle="tooltip">
                                           <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </a>
                                    {/if}
                                </td>
                                <td class="text-center plugin-frontend-links">
                                    {if $plugin->getLinkCount() > 0}
                                        <a href="links.php?kPlugin={$plugin->getID()}"
                                           class="btn btn-link"
                                           title="{__('modify')}"
                                           data-toggle="tooltip">
                                            <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </a>
                                    {/if}
                                </td>
                                <td class="text-center plugin-license">
                                    {if $plugin->hasLicenseCheck()}
                                        <button name="lizenzkey" type="submit" title="{__('modify')}"
                                                class="btn btn-link" value="{$plugin->getID()}" data-toggle="tooltip">
                                            <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </button>
                                    {/if}
                                </td>
                                <td class="text-center plugin-config">
                                    {assign var=btnGroup value=false}
                                    {if $plugin->getOptionsCount() > 0 || $plugin->isUpdateAvailable()}
                                        {assign var=btnGroup value=true}
                                    {/if}
                                    <div class="btn-group">
                                        {if $plugin->getOptionsCount() > 0}
                                            <a class="btn btn-link px-1" href="plugin.php?kPlugin={$plugin->getID()}" title="{__('settings')}" data-toggle="tooltip">
                                                <span class="icon-hover">
                                                    <span class="fal fa-cogs"></span>
                                                    <span class="fas fa-cogs"></span>
                                                </span>
                                            </a>
                                        {elseif $plugin->getLicenseMD() || $plugin->getReadmeMD()}
                                            <a class="btn btn-link px-1" href="plugin.php?kPlugin={$plugin->getID()}" title="{__('docu')}" data-toggle="tooltip">
                                                <span class="icon-hover">
                                                    <span class="fal fa-copy"></span>
                                                    <span class="fas fa-copy"></span>
                                                </span>
                                            </a>
                                            {*<a class="btn btn-default btn-sm" href="plugin.php?kPlugin={$plugin->getID()}" title="Dokumentation"><i class="fa fa-file-text-o"></i></a>*}
                                        {/if}
                                        {if $plugin->isUpdateAvailable()}
                                            <a onclick="ackCheck({$plugin->getID()});return false;" class="btn btn-link px-1" title="{__('pluginBtnUpdate')}" data-toggle="tooltip">
                                                <span class="icon-hover">
                                                    <span class="fal fa-refresh"></span>
                                                    <span class="fas fa-refresh"></span>
                                                </span>
                                            </a>
                                        {/if}
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="card-footer save-wrapper save">
                    <div class="row">
                        <div class="col-sm-6 col-xl-auto text-left">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" />
                                <label class="custom-control-label" for="ALLMSGS1">{__('selectAll')}</label>
                            </div>
                        </div>
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button id="uninstall-enabled-plugin" name="deinstallieren" type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash-alt"></i> {__('pluginBtnDeInstall')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="deaktivieren" type="submit" class="btn btn-warning btn-block">
                                <i class="fa fa-close"></i> {__('deactivate')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>
{include file='tpl_inc/pluginverwaltung_uninstall_modal.tpl' context='enabled' selector='#enabled-plugins' button='#uninstall-enabled-plugin'}
{if $smarty.const.SAFE_MODE}
<script>
    {literal}
    function invalidatePlugin(pluginID, msg) {
        let notify = '<span title="{/literal}{__('Plugin probably flawed')}{literal} ' + msg + '" class="label text-danger" data-toggle="tooltip">'
            + '    <span class="icon-hover">'
            + '      <span class="fal fa-exclamation-triangle"></span>'
            + '      <span class="fas fa-exclamation-triangle"></span>'
            + '    </span>'
            + '</span>';
        $('[for="plugin-check-' + pluginID + '"]:first').append($(notify));
    }
    function checkPlugin(pluginID) {
        simpleAjaxCall('io.php', {
            jtl_token: JTL_TOKEN,
            io : JSON.stringify({
                name: 'pluginTestLoading',
                params : [pluginID]
            })
        }, function (result) {
            if (!result.code || result.code !== {/literal}{\JTL\Plugin\InstallCode::OK}{literal}) {
                invalidatePlugin(pluginID, result.message
                    ? result.message
                    : (result.error.message ? result.error.message : ''));
            }
        }, function (result) {
            invalidatePlugin(pluginID, result.responseJSON.message
                ? result.responseJSON.message
                : (result.responseJSON.error.message ? result.responseJSON.error.message : ''));
        }, undefined, true);
    }
    $('.check input').each(function () {
        let value = parseInt($(this).val());
        if (!isNaN(value)) {
            checkPlugin(value);
        }
    })
    {/literal}
</script>
{/if}
