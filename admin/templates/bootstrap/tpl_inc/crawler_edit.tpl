{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('Crawler')}

<div id="content">
    <form id="crawlerForm" name="crawlerForm" method="post">
        {$jtl_token}
        <input type="hidden" name="save_crawler" value="1" />
        <input type="hidden" name="id" value="{$crawler->getID()}" />
        <div class="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{if isset($crawler->getID())}{__('editCrawler')}{else}{__('createCrawler')}{/if}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive">
                    <div class="card-body" id="formtable">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="useragent">{__('crawlerUserAgent')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input id="useragent" class="form-control" name="useragent" type="text" value="{$crawler->getUserAgent()}"/>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('crawlerUserAgentHint')}</div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="description">{__('crawlerDescription')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input id="description" class="form-control" type="text" name="description" value="{$crawler->getDescription()}"/>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('crawlerDescriptionHint')}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a class="btn btn-outline-primary btn-block" href="statistik.php?s=3&tab=settings">
                                <i class="fa fa-exclamation"></i> {__('Cancel')}
                            </a>
                        </div>
                        <div class=" col-sm-6 col-xl-auto">
                            <button name="speichern"  value="{__('save')}" type="button" onclick="document.getElementById('crawlerForm').submit()" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{include file='tpl_inc/footer.tpl'}