{function sideContainerSection} {* direction, directionName, oBox_arr *}
    <div class="col-md-12">
        <div class="card">
            <form action="boxen.php" method="post">
                {$jtl_token}
                <div class="card-header">
                    <div class="subheading1">{$directionName}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-header">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="box_show" id="box_{$direction}_show" value="1"
                               {if isset($bBoxenAnzeigen.$direction) && $bBoxenAnzeigen.$direction}checked{/if}>
                        <label class="custom-control-label" for="box_{$direction}_show">{__('showContainer')}</label>
                    </div>
                </div>
                <div class="card-body">
                {if $oBox_arr|@count > 0}
                    <div class="table-responsive">
                        <table class="table table-hover table-align-top">
                            <thead>
                                <tr>
                                    <th>{__('boxTitle')}</th>
                                    <th>{__('boxType')}</th>
                                    <th>{__('boxLabel')}</th>
                                    <th>{__('status')}</th>
                                    <th>{__('sorting')}</th>
                                    <th class="text-center">{__('actions')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $oBox_arr as $oBox}
                                    {include file="tpl_inc/box_single.tpl" oBox=$oBox nPage=$nPage position=$direction}
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer py-1">
                        <input type="hidden" name="position" value="{$direction}" />
                        <input type="hidden" name="page" value="{$nPage}" />
                        <input type="hidden" name="action" value="resort" />
                        <div class="row">
                            <div class="ml-auto col-sm-6 col-xl-auto">
                                <button type="submit" value="aktualisieren" class="btn btn-primary btn-block">
                                    {__('saveWithIcon')}
                                </button>
                            </div>
                        </div>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">
                        {__('noBoxesAvailableFor')|replace:'%s':$directionName}
                    </div>
                {/if}
                </div>

            </form>
            <div class="card-footer">
                <form name="newBox_{$direction}" action="boxen.php" method="post" class="form-horizontal">
                    {$jtl_token}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="newBox_{$direction}">{__('new')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="newBox_{$direction}" name="item" class="custom-select" onchange="document.newBox_{$direction}.submit();">
                                <option value="0">{__('pleaseSelect')}</option>
                                {foreach $oVorlagen_arr as $oVorlagen}
                                    <optgroup label="{$oVorlagen->cName}">
                                        {foreach $oVorlagen->oVorlage_arr as $oVorlage}
                                            <option value="{$oVorlage->kBoxvorlage}">{$oVorlage->cName}</option>
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="position" value="{$direction}" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="new" />
                </form>
            </div>
        </div>
    </div>
{/function}

{if isset($oBoxenContainer.left) && $oBoxenContainer.left === true}
    {sideContainerSection direction='left' directionName=__('sectionLeft') oBox_arr=$oBoxenLeft_arr}
{/if}
{if isset($oBoxenContainer.right) && $oBoxenContainer.right === true}
    {sideContainerSection direction='right' directionName=__('sectionRight') oBox_arr=$oBoxenRight_arr}
{/if}