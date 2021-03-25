<script type="text/javascript">
    function changeSelect(currentSelect) {ldelim}
        switch (currentSelect.options[currentSelect.selectedIndex].value) {ldelim}
            case '1':
                document.getElementById('SelectFromDay').style.display = 'none';
                document.getElementById('SelectToDay').style.display = 'none';
                break;
            case '2':
                document.getElementById('SelectFromDay').style.display = 'none';
                document.getElementById('SelectToDay').style.display = 'none';
                break;
            case '3':
                document.getElementById('SelectFromDay').style.display = 'inline';
                document.getElementById('SelectToDay').style.display = 'inline';
                break;
            case '4':
                document.getElementById('SelectFromDay').style.display = 'inline';
                document.getElementById('SelectToDay').style.display = 'inline';
                break;
            {rdelim}
    {rdelim}

    function selectSubmit(currentSelect) {ldelim}
        var $kKampagne = currentSelect.options[currentSelect.selectedIndex].value;
        if ($kKampagne > 0) {ldelim}
            window.location.href = 'kampagne.php?detail=1&token={$smarty.session.jtl_token}&kKampagne=' + $kKampagne;
        {rdelim}
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('kampagneDetailStats')|cat:' '|cat:$oKampagne->cName}

<div id="content">
    <div class="card">
        <div class="card-body">
            <form method="post" action="kampagne.php">
                {$jtl_token}
                <input type="hidden" name="detail" value="1" />
                <input type="hidden" name="zeitraum" value="1" />
                <input type="hidden" name="kKampagne" value="{$oKampagne->kKampagne}" />

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="nAnsicht">{__('kampagneDetailView')}:</label>
                            <div class="col-sm-auto">
                                <select id="nAnsicht" name="nAnsicht" class="custom-select combo" onChange="changeSelect(this);">
                                    <option value="1"{if $smarty.session.Kampagne->nDetailAnsicht == 1} selected{/if}>{__('annual')}</option>
                                    <option value="2"{if $smarty.session.Kampagne->nDetailAnsicht == 2} selected{/if}>{__('monthly')}</option>
                                    <option value="3"{if $smarty.session.Kampagne->nDetailAnsicht == 3} selected{/if}>{__('weekly')}</option>
                                    <option value="4"{if $smarty.session.Kampagne->nDetailAnsicht == 4} selected{/if}>{__('daily')}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="kKampagne">{__('kampagneSingle')}:</label>
                            <div class="col-sm-auto">
                                <select id="kKampagne" name="kKampagne" class="custom-select combo" onChange="selectSubmit(this);">
                                    {if isset($oKampagne_arr) && $oKampagne_arr|@count > 0}
                                        {foreach $oKampagne_arr as $oKampagneTMP}
                                            <option value="{$oKampagneTMP->kKampagne}"{if $oKampagneTMP->kKampagne == $oKampagne->kKampagne} selected{/if}>{$oKampagneTMP->cName}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="SelectFromDay">{__('from')}:</label>
                            <div class="col-sm-auto mb-2">
                                <select name="cFromDay" class="custom-select combo" id="SelectFromDay">
                                    {section name=fromDay loop=32 start=1 step=1}
                                        <option value="{$smarty.section.fromDay.index}"
                                                {if $smarty.session.Kampagne->cFromDate_arr.nTag == $smarty.section.fromDay.index} selected{/if}>
                                            {$smarty.section.fromDay.index}
                                        </option>
                                    {/section}
                                </select>
                            </div>
                            <div class="col-sm-auto mb-2">
                                <select name="cFromMonth" class="custom-select combo">
                                    <option value="1"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 1} selected{/if}>{__('january')}</option>
                                    <option value="2"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 2} selected{/if}>{__('february')}</option>
                                    <option value="3"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 3} selected{/if}>{__('march')}</option>
                                    <option value="4"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 4} selected{/if}>{__('april')}</option>
                                    <option value="5"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 5} selected{/if}>{__('may')}</option>
                                    <option value="6"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 6} selected{/if}>{__('june')}</option>
                                    <option value="7"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 7} selected{/if}>{__('july')}</option>
                                    <option value="8"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 8} selected{/if}>{__('august')}</option>
                                    <option value="9"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 9} selected{/if}>{__('september')}</option>
                                    <option value="10"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 10} selected{/if}>{__('october')}</option>
                                    <option value="11"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 11} selected{/if}>{__('november')}</option>
                                    <option value="12"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 12} selected{/if}>{__('december')}</option>
                                </select>
                            </div>
                            <div class="col-sm-auto">
                                {assign var=cJahr value=$smarty.now|date_format:'%Y'}
                                <select name="cFromYear" class="custom-select combo">
                                    {section name=fromYear loop=$cJahr+1 start=2005 step=1}
                                        <option value="{$smarty.section.fromYear.index}"
                                                {if $smarty.session.Kampagne->cFromDate_arr.nJahr == $smarty.section.fromYear.index} selected{/if}>
                                            {$smarty.section.fromYear.index}
                                        </option>
                                    {/section}
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="SelectToDay">{__('kampagneDateTill')}:</label>
                            <select name="cToDay" class="custom-select combo" id="SelectToDay">
                                {section name=toDay loop=32 start=1 step=1}
                                    <option value="{$smarty.section.toDay.index}"
                                            {if $smarty.session.Kampagne->cToDate_arr.nTag == $smarty.section.toDay.index} selected{/if}>
                                        {$smarty.section.toDay.index}
                                    </option>
                                {/section}
                            </select>
                            <div class="col-sm-auto mb-2">
                                <select name="cToMonth" class="custom-select combo">
                                    <option value="1"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 1} selected{/if}>{__('january')}</option>
                                    <option value="2"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 2} selected{/if}>{__('february')}</option>
                                    <option value="3"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 3} selected{/if}>{__('march')}</option>
                                    <option value="4"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 4} selected{/if}>{__('april')}</option>
                                    <option value="5"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 5} selected{/if}>{__('may')}</option>
                                    <option value="6"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 6} selected{/if}>{__('june')}</option>
                                    <option value="7"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 7} selected{/if}>{__('july')}</option>
                                    <option value="8"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 8} selected{/if}>{__('august')}</option>
                                    <option value="9"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 9} selected{/if}>{__('september')}</option>
                                    <option value="10"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 10} selected{/if}>{__('october')}</option>
                                    <option value="11"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 11} selected{/if}>{__('november')}</option>
                                    <option value="12"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 12} selected{/if}>{__('december')}</option>
                                </select>
                            </div>
                            {assign var=cJahr value=$smarty.now|date_format:'%Y'}
                            <div class="col-sm-auto mb-2">
                                <select name="cToYear" class="custom-select combo">
                                    {section name=toYear loop=$cJahr+1 start=2005 step=1}
                                        <option value="{$smarty.section.toYear.index}"
                                                {if $smarty.session.Kampagne->cToDate_arr.nJahr == $smarty.section.toYear.index} selected{/if}>
                                            {$smarty.section.toYear.index}
                                        </option>
                                    {/section}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-auto min-w-sm">
                        <button name="submitZeitraum" type="submit" value="{__('kampagneDetailStatsBTN')}" class="btn btn-primary btn-block"
                                title="{__('kampagneDetailStatsBTN')}">
                            <i class="fal fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'detailansicht'} active{/if}" data-toggle="tab" role="tab" href="#detailansicht">{__('kampagneDetailStats')}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'detailgraphen'} active{/if}" data-toggle="tab" role="tab" href="#detailgraphen">{__('kampagneDetailGraph')}</a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="detailansicht" class="tab-pane fade {if $cTab === '' || $cTab === 'detailansicht'} active show{/if}">
                {if isset($oKampagneStat_arr) && $oKampagneStat_arr|@count > 0 && isset($oKampagneDef_arr) && $oKampagneDef_arr|@count > 0}
                    <div class="table-responsive">
                        <table class="table table-striped text-center">
                            <thead>
                                <tr>
                                    <th class="th-1"></th>
                                    {foreach $oKampagneDef_arr as $oKampagneDef}
                                        <th class="th-2">{__($oKampagneDef->cName)}</th>
                                    {/foreach}
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $oKampagneStat_arr as $kKey => $oKampagneStatDef_arr}
                                    {if $kKey !== 'Gesamt'}
                                        <tr>
                                            {if isset($oKampagneStat_arr[$kKey].cDatum)}
                                                <td>{$oKampagneStat_arr[$kKey].cDatum}</td>
                                            {/if}
                                            {foreach $oKampagneStatDef_arr as $kKampagneDef => $oKampagneStatDef_arrItem}
                                                {if $kKampagneDef !== 'cDatum'}
                                                    <td>
                                                        <a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$kKampagneDef}&cStamp={$kKey}&token={$smarty.session.jtl_token}">
                                                            {$oKampagneStat_arr[$kKey][$kKampagneDef]}
                                                        </a>
                                                    </td>
                                                {/if}
                                            {/foreach}
                                        </tr>
                                    {/if}
                                {/foreach}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>{__('kampagneOverall')}</td>
                                    {foreach $oKampagneStatDef_arr as $kKampagneDef => $oKampagneStatDef_arrItem}
                                        <td>
                                            {$oKampagneStat_arr.Gesamt[$kKampagneDef]}
                                        </td>
                                    {/foreach}
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="detailgraphen" class="tab-pane fade{if $cTab === 'detailgraphen'} active show{/if}">
                {if $Charts|@count > 0}
                    {foreach name=charts from=$Charts key=key item=Chart}
                        <div class="my-5">
                            <div class="subheading1 mb-1">{$TypeNames[$key]}:</div>
                            {if isset($headline)}
                                {assign var=hl value=$headline}
                            {else}
                                {assign var=hl value=null}
                            {/if}
                            {if isset($headline)}
                                {assign var=ylabel value=$ylabel}
                            {else}
                                {assign var=ylabel value=null}
                            {/if}
                            {include file='tpl_inc/linechart_inc.tpl' linechart=$Chart headline=$hl id=$key width='100%' height='400px' ylabel=$ylabel href=false legend=false ymin='0'}
                        </div>
                    {/foreach}
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
        </div>
    </div>
    <div class="save-wrapper card-footer">
        <div class="row">
            <div class="ml-auto col-sm-6 col-xl-auto text-left">
                <a href="kampagne.php?tab=globalestats&token={$smarty.session.jtl_token}" class="btn btn-outline-primary btn-block">{__('goBack')}</a>
            </div>
        </div>
    </div>
</div>

{if $smarty.session.Kampagne->nDetailAnsicht == 1 || $smarty.session.Kampagne->nDetailAnsicht == 2}
    <script type="text/javascript">
        document.getElementById('SelectFromDay').style.display = 'none';
        document.getElementById('SelectToDay').style.display = 'none';
    </script>
{/if}
 <script type="text/javascript">
    $(document).on('shown.bs.tab', 'a[href="#detailgraphen"]', function(e) {
        $(window).trigger('resize');
    });
</script>
