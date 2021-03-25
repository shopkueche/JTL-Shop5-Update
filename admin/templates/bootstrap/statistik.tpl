{include file='tpl_inc/header.tpl'}

<script type="text/javascript">
    function changeStatType(elem) {ldelim}
        window.location.href = "statistik.php?s=" + elem.options[elem.selectedIndex].value;
    {rdelim}
</script>
{if $nTyp === $smarty.const.STATS_ADMIN_TYPE_BESUCHER}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('visitors')}
    {assign var=cURL value=__('statisticBesucherURL')}
{elseif $nTyp === $smarty.const.STATS_ADMIN_TYPE_KUNDENHERKUNFT}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('statisticKundenherkunft')}
    {assign var=cURL value=__('statisticKundenherkunftURL')}
{elseif $nTyp === $smarty.const.STATS_ADMIN_TYPE_SUCHMASCHINE}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('statisticSuchmaschine')}
    {assign var=cURL value=__('statisticSuchmaschineURL')}
{elseif $nTyp === $smarty.const.STATS_ADMIN_TYPE_UMSATZ}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('statisticUmsatz')}
    {assign var=cURL value=__('statisticUmsatzURL')}
{elseif $nTyp === $smarty.const.STATS_ADMIN_TYPE_EINSTIEGSSEITEN}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('statisticEinstiegsseite')}
    {assign var=cURL value=__('statisticEinstiegsseiteURL')}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('statisticDesc') cDokuURL=$cURL}
<div id="content">
    {if $nTyp === $smarty.const.STATS_ADMIN_TYPE_SUCHMASCHINE}
        {include file='tpl_inc/statistik_suchmaschinen.tpl'}
    {else}
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
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
