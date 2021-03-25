<div class="widget-custom-data table-responsive">
    <table class="table table-condensed table-hover table-blank">
        <tbody>
            <tr>
                <td>{__('domain')}</td>
                <td>{$cShopHost}</td>
                <td></td>
            </tr>
            <tr>
                <td>{__('host')}</td>
                <td>{$serverHTTPHost} ({$serverAddress})</td>
                <td></td>
            </tr>
            <tr>
                <td>{__('system')}</td>
                <td>{$phpOS}</td>
                <td></td>
            </tr>
            <tr>
                <td>{__('phpVersion')}</td>
                <td>{$phpVersion}</td>
                <td></td>
            </tr>
            {if isset($mySQLStats) && $mySQLStats !== '-'}
                <tr>
                    <td class="nowrap">{__('mysqlStatistic')}</td>
                    <td class="small">{$mySQLStats}</td>
                    <td></td>
                </tr>
            {/if}
            <tr>
                <td class="nowrap">{__('mysqlVersion')}</td>
                <td>{$mySQLVersion}</td>
                <td class="text-right">
                    {if $mySQLVersion < 5}
                        <a class="label label-warning" href="status.php" title="{__('moreInfo')}">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i><span class="sr-only">{__('warning')}</span>
                        </a>
                    {/if}
                </td>
            </tr>
        </tbody>
    </table>
</div>
