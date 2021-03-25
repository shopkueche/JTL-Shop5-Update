{if $oSubscription || $oVersion}
    <table class="table table-condensed table-hover table-blank">
        <tbody>
            {if $oSubscription}
                <tr>
                    <td width="50%">{__('subscriptionValidUntil')}</td>
                    <td width="50%" id="subscription">
                        {if $oSubscription->nDayDiff < 0}
                            <a href="https://jtl-url.de/subscription" target="_blank">{__('expired')}</a>
                        {else}
                            {$oSubscription->dDownloadBis_DE}
                        {/if}
                    </td>
                </tr>
            {/if}
            {if $oVersion}
                <tr>
                    <td width="50%"></td>
                    <td width="50%" id="version">
                        {if $bUpdateAvailable}
                            <span class="label label-info">{__('version')} {$strLatestVersion} {if $oVersion->build > 0}({__('build')}: {$oVersion->build}){/if} {__('available')}.</span>
                        {else}
                            <span class="label label-success">{__('shopVersionUpToDate')}</span>
                        {/if}
                    </td>
                </tr>
            {/if}
        </tbody>
    </table>
{/if}
