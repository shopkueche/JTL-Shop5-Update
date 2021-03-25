{literal}
<script type="text/javascript">
    $(document).ready(function () {
        $('.edit').on('click', function () {
            var kWarenlager = $(this).attr('id').replace('btn_', ''),
                row = $('.row_' + kWarenlager);
            if (row.css('display') === 'none') {
                row.fadeIn();
            } else {
                row.fadeOut();
            }
        });
    });
</script>
{/literal}

<div id="content">
    {if $warehouses|@count > 0}
        <form method="post" action="warenlager.php">
            {$jtl_token}
            <input name="a" type="hidden" value="update" />
            <div class="card">
                <div class="card-header">
                    <span class="subheading1">{__('warenlager')}</span>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="checkext">{__('warenlagerActive')}</th>
                                <th>{__('warenlagerIntern')}</th>
                                <th>{__('description')}</th>
                                <th class="text-center">{__('options')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $warehouses as $warehouse}
                                <tr>
                                    <td class="checkext">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="kWarenlager[]" type="checkbox" id="store-id-{$warehouse->kWarenlager}" value="{$warehouse->kWarenlager}"{if $warehouse->nAktiv == 1} checked{/if} />
                                            <label class="custom-control-label" for="store-id-{$warehouse->kWarenlager}"></label>
                                        </div>
                                    </td>
                                    <td class="large">{$warehouse->cName}</td>
                                    <td>{$warehouse->cBeschreibung}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a class="btn btn-link px-2"
                                               data-toggle="collapse"
                                               href="#collapse-{$warehouse->kWarenlager}"
                                               title="{__('edit')}"
                                               aria-expanded="false">
                                                <span class="fal fa-chevron-circle-down rotate-180 font-size-lg"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse" id="collapse-{$warehouse->kWarenlager}">
                                    <td colspan="4" class="border-top-0">
                                    {foreach $availableLanguages as $language}
                                        {assign var=kSprache value=$language->getId()}
                                        <div class="form-group form-row align-items-center mb-5 mb-md-3">
                                            <label class="col col-sm-4 col-form-label text-sm-right order-1" for="cNameSprache[{$warehouse->kWarenlager}][{$kSprache}]">{$language->getLocalizedName()}:</label>
                                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                <input id="cNameSprache[{$warehouse->kWarenlager}][{$kSprache}]"
                                                       name="cNameSprache[{$warehouse->kWarenlager}][{$kSprache}]"
                                                       type="text"
                                                       value="{if isset($warehouse->cSpracheAssoc_arr[$kSprache])}{$warehouse->cSpracheAssoc_arr[$kSprache]}{/if}"
                                                       class="form-control large" />
                                            </div>
                                        </div>
                                    {/foreach}
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="update" type="submit" title="{__('update')}" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
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
