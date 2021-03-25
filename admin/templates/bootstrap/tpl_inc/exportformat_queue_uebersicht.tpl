{include file='tpl_inc/seite_header.tpl' cTitel=__('exportformat') cBeschreibung=__('exportformatDesc') cDokuURL=__('exportformatUrl')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || empty($cTab) || $cTab === 'aktiv'} active{/if}" data-toggle="tab" role="tab" href="#aktiv">
                        {__('exportformatQueue')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'fertig'} active{/if}" data-toggle="tab" role="tab" href="#fertig">
                        {__('exportformatTodaysWork')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="aktiv" class="tab-pane fade{if $cTab === '' || $cTab === 'aktiv'} active show{/if}">
                <form method="post" action="exportformat_queue.php">
                    {$jtl_token}
                    <div>
                        <div class="subheading1">{__('exportformatQueue')}</div>
                        <hr class="mb-3">
                        {if $oExportformatCron_arr && $oExportformatCron_arr|@count > 0}
                            <div id="tabellenLivesuche" class="table-responsive">
                                <table class="table table-striped table-align-top">
                                    <thead>
                                        <tr>
                                            <th class="text-left" style="width: 10px;">&nbsp;</th>
                                            <th class="text-left">{__('exportformat')}</th>
                                            <th class="text-left">{__('exportformatOptions')}</th>
                                            <th class="text-center">{__('exportformatStart')}</th>
                                            <th class="text-center">{__('repetition')}</th>
                                            <th class="text-center">{__('exportformatExported')}</th>
                                            <th class="text-center">{__('exportformatLastStart')}</th>
                                            <th class="text-center">{__('exportformatNextStart')}</th>
                                            <th class="text-center">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oExportformatCron_arr as $oExportformatCron}
                                        <tr>
                                            <td class="text-left">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kCron[]" type="checkbox" value="{$oExportformatCron->cronID}" id="kCron-{$oExportformatCron->cronID}" />
                                                    <label class="custom-control-label" for="kCron-{$oExportformatCron->cronID}"></label>
                                                </div>
                                            </td>
                                            <td class="text-left"><label for="kCron-{$oExportformatCron->cronID}">{$oExportformatCron->cName}</label></td>
                                            <td class="text-left">{$oExportformatCron->Sprache->getLocalizedName()}/{$oExportformatCron->Waehrung->cName}/{$oExportformatCron->Kundengruppe->cName}</td>
                                            <td class="text-center">{$oExportformatCron->dStart_de}</td>
                                            <td class="text-center">{$oExportformatCron->cAlleXStdToDays}</td>
                                            <td class="text-center">
                                                {$oExportformatCron->oJobQueue->tasksExecuted|default:0}/{$oExportformatCron->nAnzahlArtikel->nAnzahl}
                                            </td>
                                            <td class="text-center">{if $oExportformatCron->dLetzterStart_de === '00.00.0000 00:00'}-{else}{$oExportformatCron->dLetzterStart_de}{/if}</td>
                                            <td class="text-center">{if $oExportformatCron->dNaechsterStart_de === null}{__('immediately')}{else}{$oExportformatCron->dNaechsterStart_de}{/if}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="exportformat_queue.php?action=editieren&kCron={$oExportformatCron->cronID}&token={$smarty.session.jtl_token}"
                                                       class="btn btn-link px-2"
                                                       title="{__('modify')}"
                                                       data-toggle="tooltip">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-edit"></span>
                                                            <span class="fas fa-edit"></span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="action[loeschen]" type="submit" value="1" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('exportformatDelete')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="action[triggern]" type="submit" value="1" class="btn btn-outline-primary btn-block">
                                            <i class="fal fa-play-circle"></i> {__('exportformatTriggerCron')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="action[uebersicht]" type="submit" value="1" class="btn btn-outline-primary btn-block">
                                            <i class="fa fa-refresh"></i> {__('refresh')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="action[erstellen]" type="submit" value="1" class="btn btn-primary btn-block add">
                                            <i class="fa fa-share"></i> {__('exportformatAdd')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        {else}
                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="action[triggern]" type="submit" value="1" class="btn btn-outline-primary btn-block">
                                            <i class="fal fa-play-circle"></i> {__('exportformatTriggerCron')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="action[erstellen]" type="submit" value="1" class="btn btn-primary btn-block add">
                                            <i class="fa fa-share"></i> {__('exportformatAdd')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    </div>
                </form>
            </div>
            <div id="fertig" class="tab-pane fade{if $cTab === 'fertig'} active show{/if}">
                <div class="toolbar">
                    <form method="post" action="exportformat_queue.php">
                        {$jtl_token}
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="nStunden">{__('exportformatLastXHourPre')} {__('hours')}:</label>
                            <div class="col-sm-auto mb-3">
                                <input size="2" class="form-control w-100" id="nStunden" name="nStunden" type="text" value="{$nStunden}" />
                            </div>
                            <span class="col-sm-auto">
                                <button name="action[fertiggestellt]" type="submit" value="1" class="btn btn-primary btn-block">
                                    <i class="fal fa-search"></i>
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
                <div>
                    <div class="subheading1">{__('exportformatTodaysWork')}</div>
                    <hr class="mb-3">
                    <div>
                    {if $oExportformatQueueBearbeitet_arr && $oExportformatQueueBearbeitet_arr|@count > 0}
                        <div id="tabellenLivesuche" class="table-responsive">
                            <table class="table table-striped table-align-top">
                                <thead>
                                    <tr>
                                        <th class="th-1">{__('exportformat')}</th>
                                        <th class="th-2">{__('filename')}</th>
                                        <th class="th-3">{__('exportformatOptions')}</th>
                                        <th class="th-4">{__('exportformatExported')}</th>
                                        <th class="th-5">{__('exportformatLastStart')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach $oExportformatQueueBearbeitet_arr as $oExportformatQueueBearbeitet}
                                    <tr>
                                        <td>{$oExportformatQueueBearbeitet->cName}</td>
                                        <td>{$oExportformatQueueBearbeitet->cDateiname}</td>
                                        <td>
                                            {$oExportformatQueueBearbeitet->name}/{$oExportformatQueueBearbeitet->cNameWaehrung}/{$oExportformatQueueBearbeitet->cNameKundengruppe}
                                        </td>
                                        <td>{$oExportformatQueueBearbeitet->nLimitN}</td>
                                        <td>{$oExportformatQueueBearbeitet->dZuletztGelaufen_DE}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <div class="alert alert-info">{__('exportformatNoTodaysWork')}</div>
                    {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
