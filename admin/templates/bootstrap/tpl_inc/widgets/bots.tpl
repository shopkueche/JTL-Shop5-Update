<div class="widget-custom-data widget-bots">
    {if is_array($oBots_arr) && count($oBots_arr) > 0}
        <table class="table table-condensed table-hover table-blank">
            <thead>
                <tr>
                    <th>{__('name')} / {__('userAgent')}</th>
                    <th class="text-right">{__('count')}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $oBots_arr as $oBots}
                    <tr>
                        <td>
                            {if isset($oBots->cName) && $oBots->cName|strlen > 0}
                                {$oBots->cName}
                            {elseif isset($oBots->cUserAgent) && $oBots->cUserAgent|strlen > 0}
                                {$oBots->cUserAgent}
                            {else}
                                {__('unknown')}
                            {/if}
                        </td>
                        <td class="text-right">{$oBots->nCount}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        {__('moreDetailsStatistics')}
    {else}
        <div class="alert alert-info">{__('noStatisticsFound')}</div>
    {/if}
</div>
