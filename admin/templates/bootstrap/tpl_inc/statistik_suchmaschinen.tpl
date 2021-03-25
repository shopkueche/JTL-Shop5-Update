<script type="text/javascript">
    $(window).on('load', function(){
        $('input[name="selectedCrawler[]"], input[name="ALLMSGS"] ').on('change',function(){
           if ($('input[name="selectedCrawler[]"]:checked').length > 0){
               $('button[name="delete"]').prop("disabled", false);
           }else{
               $('button[name="delete"]').prop("disabled", true);
           }
        });
    });
</script>
<div class="tabs">
    <nav class="tabs-nav">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {if $cTab === '' || $cTab === 'uebersicht'} active{/if}" data-toggle="tab" role="tab" href="#uebersicht">
                    {__('statisticTitle')}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $cTab === 'settings'} active{/if}" data-toggle="tab" role="tab" href="#settings">
                    {__('crawlerOverviewTitle')}
                </a>
            </li>
        </ul>
    </nav>
    <div class="tab-content">
        <div id="uebersicht" class="tab-pane fade {if $cTab === '' || $cTab === 'uebersicht'} active show{/if}">
            <div class="card">
                <div class="card-body">
                    <div class="form-row">
                        <label class="col-sm-auto col-form-label" for="statType">{__('statisticType')}:</label>
                        <span class="col-sm-auto">
                        <select class="custom-select" name="statType" id="statType" onChange="changeStatType(this);">
                            <option value="{$smarty.const.STATS_ADMIN_TYPE_BESUCHER}"{if $nTyp === $smarty.const.STATS_ADMIN_TYPE_BESUCHER} selected{/if}>{__('visitors')}</option>
                            <option value="{$smarty.const.STATS_ADMIN_TYPE_KUNDENHERKUNFT}"{if $nTyp === $smarty.const.STATS_ADMIN_TYPE_KUNDENHERKUNFT} selected{/if}>{__('customerHeritage')}</option>
                            <option value="{$smarty.const.STATS_ADMIN_TYPE_SUCHMASCHINE}"{if $nTyp === $smarty.const.STATS_ADMIN_TYPE_SUCHMASCHINE} selected{/if}>{__('searchEngines')}</option>
                            <option value="{$smarty.const.STATS_ADMIN_TYPE_UMSATZ}"{if $nTyp === $smarty.const.STATS_ADMIN_TYPE_UMSATZ} selected{/if}>{__('sales')}</option>
                            <option value="{$smarty.const.STATS_ADMIN_TYPE_EINSTIEGSSEITEN}"{if $nTyp === $smarty.const.STATS_ADMIN_TYPE_EINSTIEGSSEITEN} selected{/if}>{__('entryPages')}</option>
                        </select>
                    </span>
                    </div>
                </div>
            </div>

            {if isset($linechart)}
                <br>
                {include file='tpl_inc/linechart_inc.tpl' linechart=$linechart headline=$headline id='linechart' width='100%'
                height='400px' ylabel=$ylabel href=false legend=false ymin='0' chartpad='1.5rem'}
            {elseif isset($piechart)}
                <br>
                {include file='tpl_inc/piechart_inc.tpl' piechart=$piechart headline=$headline id='piechart' width='100%'
                height='400px' chartpad='1.5rem'}
            {/if}
            <div class="card">
                <div class="card-body">
                    {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter cParam_arr=['s' => $nTyp]}
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination cParam_arr=['s' => $nTyp]}
                    {if isset($oStat_arr) && $oStat_arr|@count > 0}
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                <tr>
                                    {foreach $cMember_arr[0] as $cMember}
                                        <th class="text-center">{$cMember[1]}</th>
                                    {/foreach}
                                </tr>
                                </thead>
                                <tbody>
                                {foreach name=stats key=i from=$oStat_arr item=oStat}
                                    {if $i >= $nPosAb && $i < $nPosBis}
                                        <tr>
                                            {foreach name=member from=$cMember_arr[$i] key=j item=cMember}
                                                {assign var=cMemberVar value=$cMember[0]}
                                                <td class="text-center">
                                                    {if $cMemberVar === 'nCount' && $nTyp === $smarty.const.STATS_ADMIN_TYPE_UMSATZ}
                                                        {$oStat->$cMemberVar|number_format:2:',':'.'} &euro;
                                                    {elseif $cMemberVar === 'nCount'}
                                                        {$oStat->$cMemberVar|number_format:0:',':'.'}
                                                    {else}
                                                        {$oStat->$cMemberVar}
                                                    {/if}
                                                </td>
                                            {/foreach}
                                        </tr>
                                    {/if}
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <div class="alert alert-info">{__('noData')}</div>
                    {/if}
                </div>
            </div>
        </div>
        <div id="settings" class="tab-pane fade {if $cTab === 'settings'} active show{/if}">
            <div class="subheading1">{__('crawlerOverviewTitle')}</div>
            <hr class="mb-3">
            {include file='tpl_inc/pagination.tpl' pagination=$crawlerPagination cParam_arr=['s'=>$nTyp,'tab'=>'settings']}
            <form id="crawlerList" name="crawlerList" method="post" action="statistik.php?s=3&tab=settings">
                {$jtl_token}
                <div class="table-responsive">
                    <table id="category-list" class="list table table-striped">
                        <thead>
                        <tr>
                            <th class="check">&nbsp;</th>
                            <th class="text-center">{__('crawlerListUserAgent')}</th>
                            <th class=" text-center">{__('crawlerListDescription')}</th>
                            <th class="th-5 text-center">&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        {if $crawler_arr|@count}
                            {foreach $crawler_arr as $crawler}
                                <tr scope="row" class="tab_bg{$crawler@iteration % 2}">
                                    <td class="check">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" value="{$crawler->kBesucherBot}" id="crawler-cb-{$crawler->kBesucherBot}" name="selectedCrawler[]" />
                                            <label class="custom-control-label" for="crawler-cb-{$crawler->kBesucherBot}"></label>
                                        </div>
                                    </td>
                                    <td class="text-center">{$crawler->cUserAgent}</td>
                                    <td class="text-center">{$crawler->cBeschreibung}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="statistik.php?s=3&edit=1&id={$crawler->kBesucherBot}"
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
                        {else}
                            <tr>
                                <td colspan="6">
                                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                                </td>
                            </tr>
                        {/if}
                        </tbody>
                    </table>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="col-sm-6 col-xl-auto text-left">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" />
                                <label class="custom-control-label" for="ALLMSGS2">{__('globalSelectAll')}</label>
                            </div>
                        </div>
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <input name="delete_crawler" type="submit" data-id="delete_crawler" value="0" class="hidden-soft">
                            <button disabled name="delete" type="button" data-toggle="modal"  data-target=".delete-modal" value="{__('delete')}" class="btn btn-danger btn-block"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <a href="statistik.php?s=3&new=1" value="{__('newCrawler')}" class="btn btn-primary btn-block"><i class="fa fa-share"></i> {__('newCrawler')}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{include file='tpl_inc/modal_delete.tpl' modalTitle=__('deleteCrawlerModalTitle') triggerName='delete_crawler' modalID='crawler-modal'}