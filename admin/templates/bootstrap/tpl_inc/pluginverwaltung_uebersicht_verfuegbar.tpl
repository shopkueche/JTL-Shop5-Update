<script>
    // transfer our licenses(-json-object) from php into js
    var vLicenses = {if isset($szLicenses)}{$szLicenses}{else}[]{/if};
//{literal}

    $(document).ready(function() {
        token = $('input[name="jtl_token"]').val();

        // for all found licenses..
        for (var key in vLicenses) {
            // ..bind a click-handler to the plugins checkbox
            $('input[id="plugin-check-'+key+'"]').on('click', function(event) {
                // grab the element, which was rising that click-event (click to the checkbox)
                var oTemp = $(event.currentTarget);
                szPluginName = oTemp.val();

                if (this.checked) { // it's checked yet, right after the click was fired
                    $('input[id="plugin-check-'+szPluginName+'"]').attr('disabled', 'disabled'); // block the checkbox!
                    $('div[id="licenseModal"]').modal({backdrop : 'static'}); // set our modal static (a click in black did not hide it!)
                    startSpinner();
                    $('div[id="licenseModal"]').find('.modal-body').load(
                        'getMarkdownAsHTML.php',
                        {'jtl_token':token, 'path':vLicenses[szPluginName]},
                        function () {
                        stopSpinner();
                    });
                    $('div[id="licenseModal"]').modal('show');
                }
            });
        }

        // handle the (befor-)hiding of the modal and what's happening during it occurs
        $('div[id="licenseModal"]').on('hide.bs.modal', function(event) {
            // IMPORTANT: release the checkbox on modal-close again too!
            $('input[id=plugin-check-'+szPluginName+']').removeAttr('disabled');

            // check, which element is 'active' before/during the modal goes hiding (to determine, which button closes it)
            // (it is faster than check a var or bind an event to an element)
            if ('ok' === document.activeElement.name) {
                $('input[id=plugin-check-'+szPluginName+']').prop('checked', true);
            } else {
                $('input[id=plugin-check-'+szPluginName+']').prop('checked', false);
            }
        });
    });
</script>
{/literal}

<div id="verfuegbar" class="tab-pane fade {if $cTab === 'verfuegbar'} active show{/if}">
    {if $pluginsAvailable->count() > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php" id="available-plugins">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div>
                <div class="subheading1">{__('pluginListNotInstalled')}</div>
                <hr class="mb-3">
                <div class="table-responsive">
                    <!-- license-modal definition -->
                    <div id="licenseModal" class="modal fade" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title">{__('licensePlugin')}</h2>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <i class="fal fa-times"></i>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    {* license.md content goes here via js *}
                                </div>
                                <div class="modal-footer">
                                    <div class="row">
                                        <div class="ml-auto col-sm-6 col-xl-auto">
                                            <button type="button" class="btn btn-outline-primary" name="cancel" data-dismiss="modal">
                                                <i class="fa fa-close"></i>&nbsp;{__('Cancel')}
                                            </button>
                                        </div>
                                        <div class="col-sm-6 col-xl-auto">
                                            <button type="button" class="btn btn-primary" name="ok" data-dismiss="modal">
                                                <i class="fal fa-check text-success"></i>&nbsp;{__('ok')}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-striped table-align-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="text-left">{__('pluginName')}</th>
                                <th class="text-center">{__('pluginCompatibility')}</th>
                                <th class="text-center">{__('pluginVersion')}</th>
                                <th>{__('pluginFolder')}</th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsAvailable as $listingItem}
                            <tr class="plugin">
                                <td class="check">
                                    <div class="custom-control custom-checkbox">
                                        <input type="hidden" id="plugin-ext-{$listingItem->getDir()}" name="isExtension[]" value="{if $listingItem->isLegacy()}0{else}1{/if}">
                                        <input class="custom-control-input" type="checkbox" name="cVerzeichnis[]" id="plugin-check-{$listingItem->getDir()}" value="{$listingItem->getDir()}" />
                                        <label class="custom-control-label" for="plugin-check-{$listingItem->getDir()}"></label>
                                    </div>
                                    {if $listingItem->isShop5Compatible() === false}
                                        {if $listingItem->isShop4Compatible() === false}
                                            <span title="{__('dangerPluginNotCompatibleShop4')}" class="label text-danger" data-toggle="tooltip">
                                                <span class="icon-hover">
                                                    <span class="fal fa-exclamation-triangle"></span>
                                                    <span class="fas fa-exclamation-triangle"></span>
                                                </span>
                                            </span>
                                        {else}
                                            <span title="{__('dangerPluginNotCompatibleShop5')}" class="label text-warning" data-toggle="tooltip">
                                                <span class="icon-hover">
                                                    <span class="fal fa-exclamation-triangle"></span>
                                                    <span class="fas fa-exclamation-triangle"></span>
                                                </span>
                                            </span>
                                        {/if}
                                    {/if}
                                </td>
                                <td>
                                    <label for="plugin-check-{$listingItem->getDir()}">{$listingItem->getName()}</label>
                                    <p><small>{$listingItem->getDescription()}</small></p>
                                    {if $listingItem->isShop4Compatible() === false && $listingItem->isShop5Compatible() === false}
                                        <div class="alert alert-info">{__('dangerPluginNotCompatibleShop4')}</div>
                                    {/if}
                                </td>
                                <td class="text-center">{$listingItem->displayVersionRange()}</td>
                                <td class="text-center">{$listingItem->getVersion()}</td>
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
                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessagesExcept(this.form, vLicenses);" />
                                <label class="custom-control-label" for="ALLMSGS4">{__('selectAll')}</label>
                            </div>
                        </div>
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="deinstallieren" id="uninstall-available-plugin" type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash-alt"></i> {__('pluginBtnDelete')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="installieren" type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-share"></i> {__('pluginBtnInstall')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        {include file='tpl_inc/pluginverwaltung_delete_modal.tpl' context='available' selector='#available-plugins' button='#uninstall-available-plugin'}
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>
