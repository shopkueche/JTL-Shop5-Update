{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('suchspecials') cBeschreibung=__('suchspecialsDesc') cDokuURL=__('suchspecialURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl' action='suchspecials.php'}
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'suchspecials'} active{/if}" data-toggle="tab" role="tab" href="#suchspecials">
                        {__('suchspecials')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="suchspecials" class="tab-pane fade {if $cTab === '' || $cTab === 'suchspecials'} active show{/if}">
                <form name="suchspecials" method="post" action="suchspecials.php">
                    {$jtl_token}
                    <div id="settings" class="settings">
                        <div class="subheading1">{__('suchspecials')}</div>
                        <hr class="mb-3">
                        <div>
                            <input type="hidden" name="suchspecials" value="1" />
                            <div class="item form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="bestseller">{__('bestseller')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" name="bestseller" id="bestseller" type="text" value="{if isset($oSuchSpecials_arr[1])}{$oSuchSpecials_arr[1]}{/if}" />
                                </div>
                            </div>
                            <div class="item form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="sonderangebote">{__('specialOffers')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" id="sonderangebote" name="sonderangebote" type="text" value="{if isset($oSuchSpecials_arr[2])}{$oSuchSpecials_arr[2]}{/if}" />
                                </div>
                            </div>
                            <div class="item form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="neu_im_sortiment">{__('newInAssortment')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" id="neu_im_sortiment" name="neu_im_sortiment" type="text" value="{if isset($oSuchSpecials_arr[3])}{$oSuchSpecials_arr[3]}{/if}" />
                                </div>
                            </div>
                            <div class="item form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="top_angebote">{__('topOffers')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" id="top_angebote" name="top_angebote" type="text" value="{if isset($oSuchSpecials_arr[4])}{$oSuchSpecials_arr[4]}{/if}" />
                                </div>
                            </div>
                            <div class="item form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="in_kuerze_verfuegbar">{__('shortTermAvailable')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" id="in_kuerze_verfuegbar" name="in_kuerze_verfuegbar" type="text" value="{if isset($oSuchSpecials_arr[5])}{$oSuchSpecials_arr[5]}{/if}" />
                                </div>
                            </div>
                            <div class="item form-group form-row align-items-center">
                                <label class="col col-sm-4 col-form-label text-sm-right" for="top_bewertet">{__('topreviews')}:</label>
                                <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                    <input class="form-control" id="top_bewertet" name="top_bewertet" type="text" value="{if isset($oSuchSpecials_arr[6])}{$oSuchSpecials_arr[6]}{/if}" />
                                </div>
                            </div>
                        </div>
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div id="einstellungen" class="tab-pane fade {if $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='suchspecials.php' buttonCaption=__('saveWithIcon') title=__('settings') tab='einstellungen'}
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
