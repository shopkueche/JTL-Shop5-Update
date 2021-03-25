{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('permissioncheck') cBeschreibung=__('permissioncheckDesc') cDokuURL=__('permissioncheckURL')}
<div id="content">
    {if isset($cDirAssoc_arr) && $cDirAssoc_arr|@count > 0}
        <div class="alert alert-info">
            <strong>{__('dirCount')}</strong> {$oStat->nCount}<br />
            <strong>{__('dirCountNotWriteable')}</strong> {$oStat->nCountInValid}
        </div>
        <ul class="list-group">
            {foreach name=dirs from=$cDirAssoc_arr key=cDir item=isValid}
                <li class="filestate list-group-item mod{$smarty.foreach.dirs.iteration%2} {if $isValid}unmodified{else}modified{/if}">
                    {if $isValid}
                        <i class="fal fa-check text-success"></i>
                    {else}
                        <i class="fal fa-exclamation-triangle text-danger"></i>
                    {/if}
                    <span class="dir-check ml-2">{$cDir}</span>
                </li>
            {/foreach}
        </ul>
        {if $oStat->nCountInValid > 0}
            <div class="save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button id="viewAll" name="viewAll" type="button" class="btn btn-primary btn-block d-none" value="{__('showAll')}">
                            <i class="fa fa-"></i> {__('showAll')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button id="viewModified" name="viewModified" type="button" class="btn btn-outline-primary btn-block viewModified" value="{__('showModified')}">
                            <i class="fal fa-exclamation-triangle"></i> {__('showModified')}
                        </button>
                    </div>
                </div>
            </div>
        {/if}
    {/if}
</div>
<script>
    {literal}
    $(document).ready(function () {
        $('#viewAll').on('click', function () {
            $('#viewAll').hide();
            $('#viewModified').show().removeClass('d-none');
            $('.unmodified').show();
            $('.modified').show();
            colorLines();
        });

        $('#viewModified').on('click', function () {
            $('#viewAll').show().removeClass('d-none');
            $('#viewModified').hide();
            $('.unmodified').hide();
            $('.modified').show();
            colorLines();
        });

        function colorLines() {
            var mod = 1;
            $('.req li:not(:hidden)').each(function () {
                if (mod === 1) {
                    $(this).removeClass('mod0');
                    $(this).removeClass('mod1');
                    $(this).addClass('mod1');
                    mod = 0;
                } else {
                    $(this).removeClass('mod1');
                    $(this).removeClass('mod0');
                    $(this).addClass('mod0');
                    mod = 1;
                }
            });
        }
    });
    {/literal}
</script>
{include file='tpl_inc/footer.tpl'}
