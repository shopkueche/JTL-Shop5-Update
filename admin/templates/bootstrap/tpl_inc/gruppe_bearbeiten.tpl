{assign var=cTitel value=__('gruppeNeu')}
{if isset($oAdminGroup) && $oAdminGroup->kAdminlogingruppe > 0}
    {assign var=cTitel value=__('gruppeBearbeiten')}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('benutzerDesc')}
<div id="content">
    <form class="settings navbar-form" action="benutzerverwaltung.php" method="post">
        {$jtl_token}
        <input type="hidden" name="tab" value="group_view" />
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('general')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="form-group form-row align-items-center{if isset($cError_arr.cGruppe)} form-error{/if}">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cGruppe">{__('name')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" name="cGruppe" id="cGruppe" value="{if isset($oAdminGroup->cGruppe)}{$oAdminGroup->cGruppe}{/if}" />
                    </div>
                </div>
                <div class="form-group form-row align-items-center{if isset($cError_arr.cBeschreibung)} form-error{/if}">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cBeschreibung">{__('description')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" id="cBeschreibung" name="cBeschreibung" value="{if isset($oAdminGroup->cBeschreibung)}{$oAdminGroup->cBeschreibung}{/if}" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('permissions')}</div>
                <hr class="mb-3">
            </div>
            <div class="card-body row">
                {foreach $permissions as $mainGroup}
                    <div id="settings-{$mainGroup@iteration}" class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <div class="subheading1">{$mainGroup->name}</div>
                                <hr class="mb-n3">
                            </div>
                            <div class="card-body">
                                {foreach $mainGroup->children as $group}
                                    <div class="mb-3">
                                        {if $group->name !== ''}<div class="subheading2">{$group->name}</div>{/if}
                                        {foreach $group->permissions as $permission}
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="checkbox" name="perm[]" value="{$permission->cRecht}" id="{$permission->cRecht}" {if isset($cAdminGroupPermission_arr) && is_array($cAdminGroupPermission_arr)}{if $permission->cRecht|in_array:$cAdminGroupPermission_arr}checked="checked"{/if}{/if} />
                                                <label class="custom-control-label" for="{$permission->cRecht}" class="perm">
                                                    {if isset($bDebug) && $bDebug} - {$permission->cRecht}{/if}
                                                    {if isset($permission->name)}
                                                        {$permission->name}
                                                    {else}
                                                        {__("permission_{$permission->cRecht}")}
                                                    {/if}
                                                </label>
                                            </div>
                                        {/foreach}
                                    </div>
                                {/foreach}
                            </div>
                            <div class="card-footer">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" onclick="checkToggle('#settings-{$mainGroup@iteration}');" id="cbtoggle-{$mainGroup@iteration}" />
                                    <label class="custom-control-label" for="cbtoggle-{$mainGroup@iteration}">{__('globalSelectAll')}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="col-sm-6 col-xl-auto text-left">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" onclick="AllMessages(this.form);" id="ALLMSGS" name="ALLMSGS" />
                            <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                        </div>
                    </div>
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <input type="hidden" name="action" value="group_edit" />
                        {if isset($oAdminGroup) && $oAdminGroup->kAdminlogingruppe > 0}
                            <input type="hidden" name="kAdminlogingruppe" value="{$oAdminGroup->kAdminlogingruppe}" />
                        {/if}
                        <input type="hidden" name="save" value="1" />
                        <a class="btn btn-outline-primary btn-block" href="benutzerverwaltung.php?tab=group_view">
                            {__('cancelWithIcon')}
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" value="{$cTitel}" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

