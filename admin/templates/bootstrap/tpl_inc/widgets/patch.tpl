<script type="text/javascript">
    $(document).ready(function () {
        ioCall(
            'getRemoteData',
            ['{$smarty.const.JTLURL_GET_SHOPPATCH}?vf={$version}',
                'oPatch_arr',
                'widgets/patch_data.tpl',
                'patch_data_wrapper'],
            undefined,
            undefined,
            undefined,
            true
        );
    });
</script>

<div class="widget-custom-data widget-patch">
    <div id="patch_data_wrapper">
        <p class="ajax_preloader">{__('loading')}</p>
    </div>
</div>