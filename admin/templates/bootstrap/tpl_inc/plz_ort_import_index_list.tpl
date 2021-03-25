<div class="table-responsive">
<table class="table">
    <thead>
        <tr>
            <th>{__('iso')}</th>
            <th>{__('country')}</th>
            <th>{__('continent')}</th>
            <th>{__('entries')}</th>
            <th>{__('action')}</th>
        </tr>
    </thead>
    <tbody>
{foreach $oPlzOrt_arr as $oPlzOrt}
    <tr>
        <td>{$oPlzOrt->cLandISO}</td>
        <td>{$oPlzOrt->cDeutsch}</td>
        <td>{$oPlzOrt->cKontinent}</td>
        <td>{$oPlzOrt->nPLZOrte|number_format:0:',':'.'}</td>
        <td>
            <div class="btn-group">
            {if isset($oPlzOrt->nBackup) && $oPlzOrt->nBackup > 0}
                <a class="btn btn-link px-2"
                   title="{__('plz_ort_import_reset_desc')}"
                   href="#"
                   data-callback="plz_ort_import_reset"
                   data-ref="{$oPlzOrt->cLandISO}"
                   data-toggle="tooltip">
                    <span class="icon-hover">
                        <span class="fal fa-history"></span>
                        <span class="fas fa-history"></span>
                    </span>
                </a>
            {/if}
            {if isset($oPlzOrt->cImportFile)}
                <a class="btn btn-link px-2"
                   title="{__('plz_ort_import_refresh_desc')}"
                   href="#"
                   data-callback="plz_ort_import_refresh"
                   data-ref="{$oPlzOrt->cImportFile}"
                   data-toggle="tooltip">
                    <span class="icon-hover">
                        <span class="fal fa-download"></span>
                        <span class="fas fa-download"></span>
                    </span>
                </a>
            {/if}
            </div>
        </td>
    </tr>
{/foreach}
    </tbody>
</table>