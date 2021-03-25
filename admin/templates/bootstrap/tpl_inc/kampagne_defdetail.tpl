{include file='tpl_inc/seite_header.tpl' cTitel=__('kampagneDetailStatsDef')}
<div id="content">
    <div class="card">
        <div class="card-header">
            <div class="subheading1"> {__($oKampagneDef->cName)}</div>
            <hr class="mb-3">
            {__('kampagnePeriod')}: {$cStampText}<br />
            {__('kampagneOverall')}: {$nGesamtAnzahlDefDetail}
        </div>
        <div class="card-body">
            {if isset($oKampagneStat_arr) && $oKampagneStat_arr|@count > 0 && isset($oKampagneDef->kKampagneDef) && $oKampagneDef->kKampagneDef > 0}
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiDefDetail
                         cParam_arr=['kKampagne'=>$oKampagne->kKampagne, 'defdetail'=>1,
                                     'kKampagneDef'=>$oKampagneDef->kKampagneDef, 'cZeitParam'=>$cZeitraumParam,
                                     'token'=>$smarty.session.jtl_token]}
                <div id="tabellenLivesuche" class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                {foreach $cMember_arr as $cMemberAnzeige}
                                    <th class="th-2">{$cMemberAnzeige|truncate:50:'...'}</th>
                                {/foreach}
                            </tr>
                        </thead>
                        <tbody>
                        {foreach $oKampagneStat_arr as $oKampagneStat}
                            <tr>
                                {foreach name='kampagnendefs' from=$cMember_arr key=cMember item=cMemberAnzeige}
                                    <td>{$oKampagneStat->$cMember|wordwrap:40:'<br />':true}</td>
                                {/foreach}
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-xl-auto">
                        <a class="btn btn-outline-primary btn-block" href="kampagne.php?kKampagne={$oKampagne->kKampagne}&detail=1&token={$smarty.session.jtl_token}">
                            {__('goBack')}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
