{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('warenkorbpers') cBeschreibung=__('warenkorbpersDesc') cDokuURL=__('warenkorbpersURL')}
<div id="content">
    {if $step === 'uebersicht'}
        <div class="card">
            <div class="card-body">
                <div class="search-toolbar mb-3">
                    <form name="suche" method="post" action="warenkorbpers.php">
                        {$jtl_token}
                        <input type="hidden" name="Suche" value="1" />
                        <input type="hidden" name="tab" value="warenkorbpers" />
                        {if isset($cSuche) && $cSuche|strlen > 0}
                            <input type="hidden" name="cSuche" value="{$cSuche}" />
                        {/if}

                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="cSuche">{__('warenkorbpersClientName')}:</label>
                            <div class="col-sm-auto mb-2">
                                <input class="form-control" id="cSuche" name="cSuche" type="text" value="{if isset($cSuche) && $cSuche|strlen > 0}{$cSuche}{/if}" />
                            </div>
                            <span class="col-sm-auto">
                                <button name="submitSuche" type="submit" value="{__('warenkorbpersSearchBTN')}" class="btn btn-primary btn-block">
                                    <i class="fal fa-search"></i>
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
                {if isset($oKunde_arr) && $oKunde_arr|@count > 0}
            {assign var=cParam_arr value=[]}
            {if isset($cSuche)}
                {append var=cParam_arr index='cSuche' value=$cSuche}
            {/if}
            {include file='tpl_inc/pagination.tpl' pagination=$oPagiKunden cParam_arr=$cParam_arr}
            <div>
                <div class="subheading1">{__('warenkorbpers')}</div>
                <hr class="mb-3">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th class="text-left">{__('warenkorbpersCompany')}</th>
                            <th class="text-left">{__('warenkorbpersClientName')}</th>
                            <th class="th-3 text-center">{__('warenkorbpersCount')}</th>
                            <th class="th-4 text-center">{__('warenkorbpersDate')}</th>
                            <th class="th-5 text-center">{__('warenkorbpersAction')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oKunde_arr as $oKunde}
                            <tr>
                                <td>{$oKunde->cFirma}</td>
                                <td>{$oKunde->cVorname} {$oKunde->cNachname}</td>
                                <td class="text-center">{$oKunde->nAnzahl}</td>
                                <td class="text-center">{$oKunde->Datum}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="warenkorbpers.php?l={$oKunde->kKunde}&token={$smarty.session.jtl_token}"
                                           class="btn btn-link px-2"
                                           data-toggle="tooltip"
                                            title="{__('delete')}">
                                            <span class="icon-hover">
                                                <span class="fal fa-trash-alt"></span>
                                                <span class="fas fa-trash-alt"></span>
                                            </span>
                                        </a>
                                        <a href="warenkorbpers.php?a={$oKunde->kKunde}&token={$smarty.session.jtl_token}"
                                           class="btn btn-link px-2"
                                           data-toggle="tooltip"
                                           title="{__('preview')}">
                                            <span class="icon-hover">
                                                <span class="fal fa-eye"></span>
                                                <span class="fas fa-eye"></span>
                                            </span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        {else}
            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
        {/if}
            </div>
        </div>
    {elseif $step === 'anzeigen'}
        {assign var=pAdditional value="&a="|cat:$kKunde}
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('warenkorbpersClient')} {$oWarenkorbPersPos_arr[0]->cVorname} {$oWarenkorbPersPos_arr[0]->cNachname}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiWarenkorb cParam_arr=['a'=>$kKunde]}
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th class="text-left">{__('warenkorbpersProduct')}</th>
                            <th class="th-2 text-center">{__('warenkorbpersCount')}</th>
                            <th class="th-3 text-center">{__('warenkorbpersDate')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oWarenkorbPersPos_arr as $oWarenkorbPersPos}
                            <tr>
                                <td class="text-left">
                                    <a href="{$shopURL}/index.php?a={$oWarenkorbPersPos->kArtikel}" target="_blank">{$oWarenkorbPersPos->cArtikelName}</a>
                                </td>
                                <td class="text-center">{$oWarenkorbPersPos->fAnzahl}</td>
                                <td class="text-center">{$oWarenkorbPersPos->Datum}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a class="btn btn-outline-primary btn-block" href="warenkorbpers.php">
                            {__('goBack')}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
