<div id="fehlerhaft" class="tab-pane fade {if $cTab === 'fehlerhaft'} active show{/if}">
    {if $pluginsErroneous->count() > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php" id="erroneous-plugins">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div>
                <div class="subheading1">{__('pluginListNotInstalledAndError')}</div>
                <hr class="mb-3">
                <div class="table-responsive">
                    <table class="table table-striped table-align-top">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="text-left">{__('pluginName')}</th>
                            <th class="text-center">{__('pluginErrorCode')}</th>
                            <th class="text-center">{__('pluginVersion')}</th>
                            <th class="text-center">{__('pluginCompatibility')}</th>
                            <th>{__('pluginFolder')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsErroneous as $listingItem}
                            <tr>
                                <td class="check">
                                    <div class="custom-control custom-checkbox">
                                        <input type="hidden" id="plugin-ext-{$listingItem->getDir()}" name="isExtension[]" value="{if $listingItem->isLegacy()}0{else}1{/if}">
                                        <input class="custom-control-input" type="checkbox" name="cVerzeichnis[]" id="plugin-err-check-{$listingItem->getDir()}" value="{$listingItem->getDir()}" />
                                        <label class="custom-control-label" for="plugin-err-check-{$listingItem->getDir()}"></label>
                                    </div>
                                </td>
                                <td>
                                    <label for="plugin-err-check-{$listingItem->getDir()}">{$listingItem->getName()}</label>
                                    <p><small>{$listingItem->getDescription()}</small></p>
                                </td>
                                <td class="text-center">
                                    <p>
                                        <span class="badge badge-danger">{$listingItem->getErrorCode()}</span>
                                        {$listingItem->getErrorMessage()}
                                    </p>
                                </td>
                                <td class="text-center">{$listingItem->getVersion()}</td>
                                <td class="text-center">{$listingItem->displayVersionRange()}</td>
                                <td>{$listingItem->getDir()}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="col-sm-6 col-xl-auto text-left">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS5" type="checkbox" onclick="AllMessagesExcept(this.form, []);" />
                                <label class="custom-control-label" for="ALLMSGS5">{__('selectAll')}</label>
                            </div>
                        </div>
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="deinstallieren" id="uninstall-erroneous-plugin" type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash-alt"></i> {__('pluginBtnDelete')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        {include file='tpl_inc/pluginverwaltung_delete_modal.tpl' context='erroneous' selector='#erroneous-plugins' button='#uninstall-erroneous-plugin'}
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>
