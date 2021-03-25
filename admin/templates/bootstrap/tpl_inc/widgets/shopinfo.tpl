<div class="widget-custom-data">
    <table class="table table-condensed table-hover table-blank">
        <tbody>
        <tr>
            <td width="50%">{__('shopVersion')}</td>
            <td width="50%" id="current_shop_version">{$strFileVersion} {if !empty($strMinorVersion)}(Build: {$strMinorVersion}){/if}</td>
        </tr>
        <tr>
            <td width="50%">{__('templateVersion')}</td>
            <td width="50%" id="current_tpl_version">{$strTplVersion}</td>
        </tr>
        <tr>
            <td width="50%">{__('dbVersion')}</td>
            <td width="50%">{$strDBVersion}</td>
        </tr>
        <tr>
            <td width="50%">{__('dbLastUpdate')}</td>
            <td width="50%">{$strUpdated}</td>
        </tr>
        </tbody>
    </table>
    <div id="version_data_wrapper">
        <p class="text-center ajax_preloader update"><i class="fa fas fa-spinner fa-spin"></i> {__('loading')}</p>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        ioCall(
            'getShopInfo',
            ['widgets/shopinfo_version.tpl', 'version_data_wrapper'],
            undefined,
            undefined,
            undefined,
            true
        );
    });
</script>
