{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('sitemapExport') cBeschreibung=__('sitemapExportDesc') cDokuURL=__('sitemapExportURL')}
<div id="content">
    <div id="confirmModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{__('danger')}!</h2>
                    <button type="button" class="close" data-dismiss="modal">
                        <i class="fal fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{__('sureDeleteEntries')}</p>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-block" name="cancel" data-dismiss="modal">{__('cancelWithIcon')}</button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button id="formSubmit" type="button" class="btn btn-danger btn-block" name="delete" ><i class="fas fa-trash-alt"></i>&nbsp;{__('delete')}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'export'} active{/if}" data-toggle="tab" role="tab" href="#export">
                        {__('sitemapExport')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'downloads'} active{/if}" data-toggle="tab" role="tab" href="#downloads">
                        {__('sitemapDownload')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'report'} active{/if}" data-toggle="tab" role="tab" href="#report">
                        {__('sitemapReport')}
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
            <div id="export" class="tab-pane fade {if $cTab === '' || $cTab === 'export'} active show{/if}">
                {if isset($errorNoWrite) && $errorNoWrite|strlen > 0}
                    <div class="alert alert-danger">{$errorNoWrite}</div>
                {/if}

                <p><input type="text" readonly="readonly" value="{$URL}" class="form-control" /></p>

                <div class="alert alert-info">
                    <p>{__('searchEnginesHint')}</p>
                    <p>{__('download')} <a href="{$URL}">{__('xml')}</a></p>
                </div>
                <div class="save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <form action="sitemap.php" method="post">
                                {$jtl_token}
                                <input type="hidden" name="update" value="1" />
                                <input type="hidden" name="tab" value="export" />

                                <button type="submit" value="{__('sitemapExportSubmit')}" class="btn btn-primary btn-block">
                                    <i class="fa fa-share"></i> {__('sitemapExportSubmit')}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div id="downloads" class="tab-pane fade {if $cTab === 'downloads'} active show{/if}">
                <div class="toolbar">
                    <form id="formDeleteSitemapExport" method="post" action="sitemapexport.php">
                        {$jtl_token}
                        <input type="hidden" name="action" value="">
                        <input type="hidden" name="tab" value="downloads">
                        <input type="hidden" name="SitemapDownload_nPage" value="0">
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="nYear-downloads">{__('year')}:</label>
                            <div class="col-sm-auto mb-3">
                                <select id="nYear-downloads" name="nYear_downloads" class="custom-select on-change-submit">
                                    {foreach $oSitemapDownloadYears_arr as $oSitemapDownloadYear}
                                        <option value="{$oSitemapDownloadYear->year}"{if isset($nSitemapDownloadYear) && $nSitemapDownloadYear == $oSitemapDownloadYear->year} selected="selected"{/if}>{$oSitemapDownloadYear->year}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <span class="col-sm-auto">
                                <button type="button" class="btn btn-danger btn-block"
                                        data-form="#formDeleteSitemapExport" data-action="year_downloads_delete" data-target="#confirmModal" data-toggle="modal" data-title="{__('sitemapDownload')} löschen">
                                    <i class="fas fa-trash-alt"></i>&nbsp;{__('delete')}
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
                {include file='tpl_inc/pagination.tpl' pagination=$oSitemapDownloadPagination cParam_arr=['tab' => 'downloads', 'nYear_downloads' => {$nSitemapDownloadYear}]}
                {if isset($oSitemapDownload_arr) && $oSitemapDownload_arr|@count > 0}
                    <div>
                        <form name="sitemapdownload" method="post" action="sitemapexport.php">
                            {$jtl_token}
                            <input type="hidden" name="download_edit" value="1" />
                            <input type="hidden" name="tab" value="downloads" />
                            <input type="hidden" name="nYear_downloads" value="{$nSitemapDownloadYear}" />
                            <div id="payment">
                                <div id="tabellenBewertung" class="table-responsive">
                                    <table class="table table-striped table-align-top">
                                        <thead>
                                            <tr>
                                                <th>&nbsp;</th>
                                                <th>{__('sitemapName')}</th>
                                                <th>{__('sitemapBot')}</th>
                                                <th class="text-right">{__('sitemapDate')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oSitemapDownload_arr as $oSitemapDownload}
                                            <tr>
                                                <td width="20">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" name="kSitemapTracker[]" type="checkbox" id="sitemap-tracker-id-{$oSitemapDownload->kSitemapTracker}" value="{$oSitemapDownload->kSitemapTracker}">
                                                        <label class="custom-control-label" for="sitemap-tracker-id-{$oSitemapDownload->kSitemapTracker}"></label>
                                                    </div>
                                                </td>
                                                <td><a href="{\JTL\Shop::getURL()}/{$oSitemapDownload->cSitemap}" target="_blank">{$oSitemapDownload->cSitemap}</a></td>
                                                <td>
                                                    <strong>{__('sitemapIP')}</strong>: {$oSitemapDownload->cIP}<br />
                                                    {if $oSitemapDownload->cBot|strlen > 0}
                                                        <strong>{__('sitemapBot')}</strong>: {$oSitemapDownload->cBot}
                                                    {else}
                                                        <strong>{__('sitemapUserAgent')}</strong>: <abbr title="{$oSitemapDownload->cUserAgent}">{$oSitemapDownload->cUserAgent|truncate:60}</abbr>
                                                    {/if}
                                                </td>
                                                <td class="text-right" width="130">{$oSitemapDownload->dErstellt_DE}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td>

                                                </td>
                                                <td colspan="6"><label for="ALLMSGS"></label></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="card-footer save-wrapper">
                                    <div class="row">
                                        <div class="col-sm-6 col-xl-auto text-left">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                                <label class="custom-control-label" for="ALLMSGS">{__('sitemapSelectAll')}</label>
                                            </div>
                                        </div>
                                        <div class="ml-auto col-sm-6 col-xl-auto">
                                            <button class="btn btn-danger btn-block" name="loeschen" type="submit" value="{__('delete')}">
                                                <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="report" class="tab-pane fade {if $cTab === 'report'} active show{/if}">
                <div class="toolbar">
                    <form id="formDeleteSitemapReport" method="post" action="sitemapexport.php">
                        {$jtl_token}
                        <input type="hidden" name="action" value="">
                        <input type="hidden" name="tab" value="report">
                        <input type="hidden" name="SitemapReport_nPage" value="0">
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="nYear-reports">{__('year')}:</label>
                            <div class="col-sm-auto mb-3">
                                <select id="nYear-reports" name="nYear_reports" class="custom-select on-change-submit">
                                    {foreach $oSitemapReportYears_arr as $oSitemapReportYear}
                                        <option value="{$oSitemapReportYear->year}"{if isset($nSitemapReportYear) && $nSitemapReportYear == $oSitemapReportYear->year} selected="selected"{/if}>{$oSitemapReportYear->year}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="col-sm-auto">
                                <button type="button" class="btn btn-danger btn-block"
                                                 data-form="#formDeleteSitemapReport" data-action="year_reports_delete" data-target="#confirmModal" data-toggle="modal" data-title="{__('sitemapReport')} löschen">
                                    <i class="fas fa-trash-alt"></i>&nbsp;{__('delete')}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                {include file='tpl_inc/pagination.tpl' pagination=$oSitemapReportPagination cParam_arr=['tab' => 'report', 'nYear_reports' => {$nSitemapReportYear}]}

                {if isset($oSitemapReport_arr) && $oSitemapReport_arr|@count > 0}
                    <div>
                        <form name="sitemapreport" method="post" action="sitemapexport.php">
                            {$jtl_token}
                            <input type="hidden" name="report_edit" value="1">
                            <input type="hidden" name="tab" value="report">
                            <input type="hidden" name="nYear_reports" value="{$nSitemapReportYear}" />
                            <div class="table-responsive">
                                <table class="table table-align-top">
                                    <thead>
                                        <tr>
                                            <th class="check"></th>
                                            <th class="text-center">{__('sitemapProcessTime')}</th>
                                            <th class="text-center">{__('sitemapTotalURL')}</th>
                                            <th class="text-center">{__('sitemapDate')}</th>
                                            <th class="th-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oSitemapReport_arr as $oSitemapReport}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kSitemapReport[]" type="checkbox" id="sitemap-report-id-{$oSitemapReport->kSitemapReport}" value="{$oSitemapReport->kSitemapReport}">
                                                    <label class="custom-control-label" for="sitemap-report-id-{$oSitemapReport->kSitemapReport}"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">{$oSitemapReport->fVerarbeitungszeit}s</td>
                                            <td class="text-center">{$oSitemapReport->nTotalURL}</td>
                                            <td class="text-center">{$oSitemapReport->dErstellt_DE}</td>
                                            <td>
                                                {if isset($oSitemapReport->oSitemapReportFile_arr) && $oSitemapReport->oSitemapReportFile_arr|@count > 0}
                                                    <a href="#" onclick="$('#info_{$oSitemapReport->kSitemapReport}').toggle();return false;">
                                                        <span class="fal fa-chevron-circle-down rotate-180 font-size-lg"></span>
                                                    </a>
                                                {else}
                                                    &nbsp;
                                                {/if}
                                            </td>
                                        </tr>
                                        {if isset($oSitemapReport->oSitemapReportFile_arr) && $oSitemapReport->oSitemapReportFile_arr|@count > 0}
                                            <tr id="info_{$oSitemapReport->kSitemapReport}" style="display: none;">
                                                <td class="border-top-0">&nbsp;</td>
                                                <td class="border-top-0" colspan="4">

                                                    <table class="table-striped" border="0" cellspacing="1" cellpadding="0" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <th>{__('sitemapName')}</th>
                                                                <th class="th-2 text-center">{__('sitemapCountURL')}</th>
                                                                <th class="th-3 text-center">{__('sitemapSize')}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {foreach $oSitemapReport->oSitemapReportFile_arr as $oSitemapReportFile}
                                                                <tr>
                                                                    <td>{$oSitemapReportFile->cDatei}</td>
                                                                    <td class="text-center">{$oSitemapReportFile->nAnzahlURL}</td>
                                                                    <td class="text-center">{$oSitemapReportFile->fGroesse} KB</td>
                                                                </tr>
                                                            {/foreach}
                                                        </tbody>
                                                    </table>

                                                </td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS2">{__('sitemapSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="einstellungen" class="tab-pane fade {if $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='sitemapexport.php' buttonCaption=__('saveWithIcon') title=__('settings') tab='einstellungen'}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#confirmModal').on('show.bs.modal', function (ev) {
        var $btn = $(ev.relatedTarget);
        var $dlg = $(this);
        $dlg.find('.modal-title').text($btn.data('title'));
        $dlg.find('#formSubmit')
            .data('form', $btn.data('form'))
            .data('action', $btn.data('action'));
    });
    $('#formSubmit').on('click', function (ev) {
        var $form = $($(this).data('form'));
        if ($form.length) {
            $form.find('input[name="action"]').val($(this).data('action'));
            $form.submit();
        }
    });
</script>
{include file='tpl_inc/footer.tpl'}
