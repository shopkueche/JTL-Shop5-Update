{include file='tpl_inc/seite_header.tpl' cTitel=__('exportformats')}
{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $('#nAlleXStunden').on('change', function () {
                var val = $(this).val(),
                    customFieldWrapper = $('#custom-freq-input-wrapper');
                if (val === 'custom') {
                    customFieldWrapper.removeClass('d-none')
                        .find('input').attr('name', 'nAlleXStundenCustom');
                } else {
                    customFieldWrapper.addClass('d-none')
                        .find('input').attr('name', '');
                }
            });
        });
    </script>
{/literal}
<div id="content" class="container-fluid2">
    <form name="exportformat_queue" method="post" action="exportformat_queue.php">
        {$jtl_token}
        {$cronID = $oCron->cronID|default:0}
        <input type="hidden" name="erstellen_eintragen" value="1" />
        {if $cronID > 0}
            <input type="hidden" name="kCron" value="{$cronID}" />
        {/if}
        {if $oExportformat_arr|@count > 0}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{if $cronID > 0}{__('save')}{else}{__('exportformatAdd')}{/if}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body" id="formtable">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kExportformat">{__('exportformats')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select name="kExportformat" id="kExportformat" class="custom-select">
                                <option value="-1"></option>
                                {foreach $oExportformat_arr as $oExportformat}
                                    <option value="{$oExportformat->kExportformat}"{if (isset($oFehler->kExportformat) && $oFehler->kExportformat == $oExportformat->kExportformat) || (isset($oCron->foreignKeyID) && $oCron->foreignKeyID == $oExportformat->kExportformat)} selected{/if}>{$oExportformat->cName}
                                        ({$oExportformat->Sprache->getLocalizedName()} / {$oExportformat->Waehrung->getName()}
                                        / {$oExportformat->Kundengruppe->getName()})
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="dStart">{__('exportformatStart')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input id="dStart" name="dStart" type="text" class="form-control" value="{if isset($oFehler->dStart) && $oFehler->dStart|strlen > 0}{$oFehler->dStart}{elseif isset($oCron->dStart_de) && $oCron->dStart_de|strlen > 0}{$oCron->dStart_de}{else}{$smarty.now|date_format:'%d.%m.%Y %H:%M'}{/if}" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nAlleXStunden">{__('exportformatEveryXHour')}:</label>
                        {assign var=showCustomInput value=false}
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="nAlleXStunden" name="nAlleXStunden" class="custom-select">
                                <option value="24"{if (isset($oFehler->nAlleXStunden) && $oFehler->nAlleXStunden == 24) || (isset($oCron->frequency) && $oCron->frequency === 24)} selected{/if}>
                                    24 {__('hours')}
                                </option>
                                <option value="48"{if (isset($oFehler->nAlleXStunden) && $oFehler->nAlleXStunden == 48) || (isset($oCron->frequency) && $oCron->frequency === 48)} selected{/if}>
                                    48 {__('hours')}
                                </option>
                                <option value="168"{if (isset($oFehler->nAlleXStunden) && $oFehler->nAlleXStunden == 168) || (isset($oCron->frequency) && $oCron->frequency === 168)} selected{/if}>
                                    1 {__('week')}
                                </option>
                                <option value="custom" id="custom-freq"{if $showCustomInput} selected{/if}>
                                    {__('own')}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div id="custom-freq-input-wrapper" class="form-group form-row align-items-center d-none">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nAlleXStundenCustom"></label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="number"
                                   min="1"
                                   value="{if !empty($oCron->frequency) && $oCron->frequency != 24 && $oCron->frequency != 48 && $oCron->frequency != 168}{assign var=showCustomInput value=true}{$oCron->frequency}{/if}"
                                   class="form-control"
                                   name="{if $showCustomInput}nAlleXStundenCustom{/if}" id="custom-freq-input"/>
                        </div>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a class="btn btn-outline-primary btn-block" href="exportformat_queue.php">
                                {__('cancelWithIcon')}
                            </a>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="action[erstellen_eintragen]" type="submit" value="1" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> {if $cronID > 0}{__('save')}{else}{__('exportformatAdd')}{/if}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        {else}
            <div class="alert alert-info">{__('exportformatNoFormat')}</div>
        {/if}
    </form>
</div>
