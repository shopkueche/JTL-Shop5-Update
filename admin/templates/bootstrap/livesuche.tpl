{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('livesearch') cBeschreibung=__('livesucheDesc') cDokuURL=__('livesucheURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl' action='livesuche.php'}
        </div>
    </div>
    <nav class="tabs-nav">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {if !isset($tab) || $tab === 'suchanfrage'} active{/if}" data-toggle="tab" role="tab" href="#suchanfrage">
                    {__('searchrequest')}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if isset($tab) && $tab === 'erfolglos'} active{/if}" data-toggle="tab" role="tab" href="#erfolglos">
                    {__('searchmiss')}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if isset($tab) && $tab === 'mapping'} active{/if}" data-toggle="tab" role="tab" href="#mapping">
                    {__('mapping')}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if isset($tab) && $tab === 'blacklist'} active{/if}" data-toggle="tab" role="tab" href="#blacklist">
                    {__('blacklist')}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if isset($tab) && $tab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                    {__('settings')}
                </a>
            </li>
        </ul>
    </nav>
    <div class="tab-content">
        <div id="suchanfrage" class="tab-pane fade {if !isset($tab) || $tab === 'suchanfrage'} active show{/if}">
            {if isset($Suchanfragen) && $Suchanfragen|@count > 0}
                <div class="search-toolbar mb-3">
                    <form class="" name="suche" method="post" action="livesuche.php">
                        {$jtl_token}
                        <input type="hidden" name="Suche" value="1" />
                        <input type="hidden" name="tab" value="suchanfrage" />
                        {if isset($cSuche) && $cSuche|strlen > 0}
                            <input name="cSuche" type="hidden" value="{$cSuche}" />
                        {/if}
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="cSuche">{__('livesucheSearchItem')}:</label>
                            <div class="col-sm-auto mb-3">
                                <input class="form-control" id="cSuche" name="cSuche" type="text" value="{if isset($cSuche) && $cSuche|strlen > 0}{$cSuche}{/if}" />
                            </div>
                            <span class="col-sm-auto">
                                <button name="submitSuche" type="submit" value="{__('search')}" class="btn btn-primary btn-block"><i class="fal fa-search"></i></button>
                            </span>
                        </div>
                    </form>
                </div>
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiSuchanfragen cAnchor='suchanfrage'}
                <form name="login" method="post" action="livesuche.php">
                    {$jtl_token}
                    <input type="hidden" name="livesuche" value="1" />
                    <input type="hidden" name="cSuche" value="{if isset($cSuche)}{$cSuche}{/if}" />
                    <input type="hidden" name="nSort" value="{$nSort}" />
                    <input type="hidden" name="tab" value="suchanfrage" />
                    {if isset($cSuche) && $cSuche|strlen > 0}
                        {assign var=pAdditional value='cSuche='|cat:$cSuche}
                    {else}
                        {assign var=pAdditional value=''}
                    {/if}
                    {if isset($cSuche)}
                        {assign var=cSuchStr value='&Suche=1&cSuche='|cat:$cSuche|cat:'&'}
                    {else}
                        {assign var=cSuchStr value=''}
                    {/if}
                    <div>
                        <div class="subheading1">{__('searchrequest')}</div>
                        <hr class="mb-3">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="th-1"></th>
                                        <th class="text-left">
                                            (<a href="livesuche.php?{$cSuchStr}nSort=1{if $nSort == 1}1{/if}&tab=suchanfrage">{if $nSort == 1}Z...A{else}A...Z{/if}</a>) {__('search')}
                                        </th>
                                        <th class="text-left">
                                            (<a href="livesuche.php?{$cSuchStr}nSort=2{if $nSort == 2 || $nSort == -1}2{/if}&tab=suchanfrage">{if $nSort == 2 || $nSort == -1}1...9{else}9...1{/if}</a>) {__('searchcount')}
                                        </th>
                                        <th class="th-4">
                                            (<a href="livesuche.php?{$cSuchStr}nSort=3{if $nSort == 3 || $nSort == -1}3{/if}&tab=suchanfrage">{if $nSort == 3 || $nSort == -1}0...1{else}1...0{/if}</a>) {__('active')}
                                        </th>
                                        <th class="th-5">{__('mapping')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach $Suchanfragen as $suchanfrage}
                                    <input name="kSuchanfrageAll[]" type="hidden" value="{$suchanfrage->kSuchanfrage}" />
                                    <tr>
                                        <td>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="checkbox" name="kSuchanfrage[]" id="search-request-id-{$suchanfrage->kSuchanfrage}" value="{$suchanfrage->kSuchanfrage}" />
                                                <label class="custom-control-label" for="search-request-id-{$suchanfrage->kSuchanfrage}"></label>
                                            </div>
                                        </td>
                                        <td>{$suchanfrage->cSuche}</td>
                                        <td>
                                            <input class="form-control fieldOther" name="nAnzahlGesuche_{$suchanfrage->kSuchanfrage}" type="text" value="{$suchanfrage->nAnzahlGesuche}" style="width:50px;" />
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="checkbox" name="nAktiv[]" id="nAktiv_{$suchanfrage->kSuchanfrage}" value="{$suchanfrage->kSuchanfrage}" {if $suchanfrage->nAktiv==1}checked="checked"{/if} />
                                                <label class="custom-control-label" for="nAktiv_{$suchanfrage->kSuchanfrage}"></label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-control fieldOther" type="text" name="mapping_{$suchanfrage->kSuchanfrage}" />
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                        <div class="save-wrapper">
                            <div class="row">
                                <div class="col-sm-6 col-xl-auto text-left">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessagesExcept(this.form, 'nAktiv_');" />
                                        <label class="custom-control-label" for="ALLMSGS">{__('livesucheSelectAll')}</label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <div class="input-group">
                                        <span class="input-group-addon d-none d-md-block">
                                            <label for="cMapping">{__('livesucheMappingOn')}:</label>
                                        </span>
                                        <input class="form-control" name="cMapping" type="text">
                                        <span class="input-group-btn ml-1">
                                            <button name="submitMapping" type="submit" value="{__('livesucheMappingOnBTN')}" class="btn btn-primary">{__('livesucheMappingOnBTN')}</button>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button name="delete" type="submit" value="{__('delete')}" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                    </button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button name="suchanfragenUpdate" type="submit" value="{__('update')}" class="btn btn-primary btn-block reset">
                                        <i class="fa fa-refresh"></i> {__('update')}
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
        <div id="erfolglos" class="tab-pane fade {if isset($tab) && $tab === 'erfolglos'} active show{/if}">
            {if $Suchanfragenerfolglos && $Suchanfragenerfolglos|@count > 0}
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiErfolglos cAnchor='erfolglos'}
                <form name="login" method="post" action="livesuche.php">
                    {$jtl_token}
                    <input type="hidden" name="livesuche" value="2">
                    <input type="hidden" name="tab" value="erfolglos">
                    <input type="hidden" name="nErfolglosEditieren" value="{if isset($nErfolglosEditieren)}{$nErfolglosEditieren}{/if}">
                    <div class="settings">
                        <div class="subheading1">{__('searchmiss')}</div>
                        <hr class="mb-3">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="th-1" style="width: 40px;">&nbsp;</th>
                                        <th class="th-1" align="left">{__('search')}</th>
                                        <th class="th-2" align="left">{__('searchcount')}</th>
                                        <th class="th-3" align="left">{__('lastsearch')}</th>
                                        <th class="th-4" align="left">{__('mapping')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach $Suchanfragenerfolglos as $Suchanfrageerfolglos}
                                    <tr>
                                        <td>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" name="kSuchanfrageErfolglos[]" type="checkbox" id="search-request-unsuccessful-id-{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" value="{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" />
                                                <label class="custom-control-label" for="search-request-unsuccessful-id-{$Suchanfrageerfolglos->kSuchanfrageErfolglos}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            {if isset($nErfolglosEditieren) && $nErfolglosEditieren == 1}
                                                <input class="form-control" name="cSuche_{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" type="text" value="{$Suchanfrageerfolglos->cSuche}" />
                                            {else}
                                                {$Suchanfrageerfolglos->cSuche}
                                            {/if}
                                        </td>
                                        <td>{$Suchanfrageerfolglos->nAnzahlGesuche}</td>
                                        <td>{$Suchanfrageerfolglos->dZuletztGesucht}</td>
                                        <td>
                                            {if !isset($nErfolglosEditieren) || $nErfolglosEditieren != 1}
                                                <input class="form-control fieldOther" name="mapping_{$Suchanfrageerfolglos->kSuchanfrageErfolglos}" type="text" />
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                        <div class="save-wrapper">
                            <div class="row">
                                <div class="col-sm-6 col-xl-auto text-left">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessagesExcept(this.form, 'nAktiv_');" />
                                        <label class="custom-control-label" for="ALLMSGS2">{__('livesucheSelectAll')}</label>
                                    </div>
                                </div>
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button class="btn btn-danger btn-block" name="erfolglosDelete" type="submit">
                                        <i class="fas fa-trash-alt"></i> {__('delete')}
                                    </button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button class="btn btn-outline-primary btn-block" name="erfolglosUpdate" type="submit">
                                        <i class="fa fa-refresh"></i> {__('update')}
                                    </button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button class="btn btn-primary btn-block" name="erfolglosEdit" type="submit">
                                        <i class="fal fa-edit"></i> {__('livesucheEdit')}
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
        <div id="mapping" class="tab-pane fade {if isset($tab) && $tab === 'mapping'} active show{/if}">
            {if $Suchanfragenmapping && $Suchanfragenmapping|@count > 0}
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiMapping cAnchor='mapping'}
                <form name="login" method="post" action="livesuche.php">
                    {$jtl_token}
                    <input type="hidden" name="livesuche" value="4" />
                    <input type="hidden" name="tab" value="mapping" />
                    <div class="settings">
                        <div class="subheading1">{__('mapping')}</div>
                        <hr class="mb-3">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="th-1"></th>
                                        <th class="th-2">{__('search')}</th>
                                        <th class="th-3">{__('searchnew')}</th>
                                        <th class="th-4">{__('searchcount')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach $Suchanfragenmapping as $sfm}
                                    <tr>
                                        <td>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" name="kSuchanfrageMapping[]" type="checkbox" id="search-mapping-id-{$sfm->kSuchanfrageMapping}" value="{$sfm->kSuchanfrageMapping}">
                                                <label class="custom-control-label" for="search-mapping-id-{$sfm->kSuchanfrageMapping}"></label>
                                            </div>
                                        </td>
                                        <td>{$sfm->cSuche}</td>
                                        <td>{$sfm->cSucheNeu}</td>
                                        <td>{$sfm->nAnzahlGesuche}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                        <div class="save-wrapper">
                            <div class="row">
                                <div class="col-sm-6 col-xl-auto text-left">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" />
                                        <label class="custom-control-label" for="ALLMSGS3">{__('globalSelectAll')}</label>
                                    </div>
                                </div>
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button name="delete" type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash-alt"></i> {__('mappingDelete')}
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
        <div id="blacklist" class="tab-pane fade {if isset($tab) && $tab === 'blacklist'} active show{/if}">
            <form name="login" method="post" action="livesuche.php">
                {$jtl_token}
                <input type="hidden" name="livesuche" value="3" />
                <input type="hidden" name="tab" value="blacklist" />

                <div class="settings">
                    <div class="subheading1">{__('blacklist')}</div>
                    <hr class="mb-3">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="th-1">{__('blacklistDescription')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="tab-1_bg">
                                    <td>
                                        <textarea class="form-control" name="suchanfrageblacklist" style="width:100%;min-height:400px;">{foreach $Suchanfragenblacklist as $Suchanfrageblacklist}{$Suchanfrageblacklist->cSuche};{/foreach}</textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="save-wrapper">
                        <div class="row">
                            <div class="ml-auto col-sm-6 col-xl-auto">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-refresh"></i> {__('update')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($tab) && $tab === 'einstellungen'} active show{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='livesuche.php' buttonCaption=__('saveWithIcon') title=__('settings') tab='einstellungen'}
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
