{capture name='testfailed'}
    <a class="label label-warning" href="systemcheck.php" title="Mehr Informationen im Systemcheck">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i><span class="sr-only">{__('warning')}</span>
    </a>
{/capture}
{capture name='testpassed'}
    <span class="label label-success">
        <i class="fal fa-check text-success" aria-hidden="true"></i><span class="sr-only">{__('ok')}</span>
    </span>
{/capture}

<div class="widget-custom-data">
    <table class="table table-condensed table-hover table-blank">
        <tbody>
            <tr>
                <td>{__('maxPHPExecutionTime')}</td>
                <td>{$maxExecutionTime}</td>
                <td class="text-right">
                    {if $bMaxExecutionTime}
                        {$smarty.capture.testpassed}
                    {else}
                        {$smarty.capture.testfailed}
                    {/if}
                </td>
            </tr>
            <tr>
                <td>{__('phpMemoryLimit')}</td>
                <td>{$memoryLimit}</td>
                <td class="text-right">
                    {if $bMemoryLimit}
                        {$smarty.capture.testpassed}
                    {else}
                        {$smarty.capture.testfailed}
                    {/if}
                </td>
            </tr>
            <tr>
                <td>{__('phpMaxTransimissionSizeFile')}</td>
                <td>{$maxFilesize}</td>
                <td class="text-right">
                    {if $bMaxFilesize}
                        {$smarty.capture.testpassed}
                    {else}
                        {$smarty.capture.testfailed}
                    {/if}
                </td>
            </tr>
            <tr>
                <td>{__('phpMaxTransimissionSizePost')}</td>
                <td>{$postMaxSize}</td>
                <td class="text-right">
                    {if $bPostMaxSize}
                        {$smarty.capture.testpassed}
                    {else}
                        {$smarty.capture.testfailed}
                    {/if}
                </td>
            </tr>
            <tr>
                <td>allow_url_fopen {__('activated')}</td>
                <td>{if $bAllowUrlFopen}{__('yes')}{else}{__('no')}{/if}</td>
                <td class="text-right">
                    {if $bAllowUrlFopen}
                        {$smarty.capture.testpassed}
                    {else}
                        {$smarty.capture.testfailed}
                    {/if}
                </td>
            </tr>
        </tbody>
    </table>
</div>
