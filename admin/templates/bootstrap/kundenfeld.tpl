{include file='tpl_inc/header.tpl'}

<script type="text/javascript">
    var kundenfeldSortDesc = "{__('kundenfeldSortDesc')}";
{literal}
    function countKundenfeldwert() {
        return $('#formtable tr.kundenfeld_wert').length;
    }

    function startKundenfeldwertEdit() {
        $('#cTyp').after($('<div class="kundenfeld_wert"></div>').append(
                $('<button name="button" type="button" class="btn btn-primary add" value="Wert hinzufügen"></button>')
                .on('click', function() {
                    addKundenfeldWert();
                })
                .append('<i class="fal fa-plus-square-o"></i>&nbsp;' + '{/literal}{__('addValue')}{literal}'))
        );
        addKundenfeldWert();
    }

    function emptyToZero() {
        var vSortValues = $('.kundenfeld_wert .field[name*="[nSort]"]')
            .map(function(key, oWertSortField) {
                if (0 === oWertSortField.value.length) {
                    oWertSortField.value = 0;
                }
            })
        ;
    }

    function recommendSort() {
        var retval       = '';
        var nWertStepLen = 1;

        emptyToZero();
        var vSortValues = $('.kundenfeld_wert .field[name*="[nSort]"]')
            .map(function() {
                return this.value;
            })
            .get()
        ;
        if (0 < vSortValues.length) {
            vSortValues
                .sort(function(val1, val2) {
                    if(Number(val1) === Number(val2)) return 0;
                    else return Number(val1) < Number(val2) ? 1 : -1;
                })
            ;
            if (1 < vSortValues.length) {
                nWertStepLen = Number(vSortValues[0] - vSortValues[1]);
            } else {
                nWertStepLen = Number(vSortValues[0]);
            }
            retval = Number(vSortValues[0]) + nWertStepLen;
        }

        return(retval);
    }

    function addKundenfeldWert() {
        var key = 0;
        while ($('.kundenfeld_wert .field[name*="cfValues[' + key + '][cWert]"]').length > 0) {
            key++;
        }

        $('#formtable tbody').append($('<tr class="kundenfeld_wert"></tr>').append(
                '<td class="kundenfeld_wert_label">' + '{/literal}{__('value')}{literal} '  + (countKundenfeldwert() + 1) + ':</td>',
                $('<td class="row"></td>').append(
                    $('<div class="col-lg-3 jtl-list-group"></div>').append(
                        '<input name="cfValues[' + key + '][cWert]" type="text" class="field form-control" value="" />'),
                    $('<div class="col-lg-2 jtl-list-group"></div>').append($('<div class="input-group" title="' + kundenfeldSortDesc + '"></div>').append(
                        '<span class="input-group-addon">' + '{/literal}{__('sortShort')}{literal}' + '</span>'
                        + '<input name="cfValues[' + key + '][nSort]" type="text" class="field form-control" value="' + recommendSort() + '" />')),
                    $('<div class="btn-group"></div>').append(
                        $('<button name="delete" type="button" class="btn btn-danger" value="Entfernen"></button>')
                            .on('click', function() {
                                delKundenfeldWert(this);
                            })
                            .append('<i class="fas fa-trash-alt"></i>&nbsp;'  + '{/literal}{__('remove')}{literal}')
                        )
                    )
                )
        );
    }

    function delKundenfeldWert(pThis) {
        if (countKundenfeldwert() > 1) {
            $(pThis).closest('tr.kundenfeld_wert').remove();
            $('#formtable tr.kundenfeld_wert td.kundenfeld_wert_label').each(function(pIndex) {
                $(this).html('{/literal}{__('value')}{literal}' + (pIndex + 1) + ':');
            });
        } else {
            alert('{/literal}{__('errorFieldNeedsAtLeastOneValue')}{literal}');
        }
    }

    function stopKundenfeldwertEdit() {
        $('#formtable .kundenfeld_wert').remove();
    }

    function selectCheck(selectBox) {
        if (selectBox.selectedIndex === 3) {
            startKundenfeldwertEdit();
        } else {
            stopKundenfeldwertEdit();
        }
    }
{/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('kundenfeld') cBeschreibung=__('kundenfeldDesc') cDokuURL=__('kundenfeldURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl' id='kSprache' action='kundenfeld.php'}
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-items">
                    <a class="nav-link {if $cTab === '' || $cTab === 'uebersicht'} active{/if}" data-toggle="tab" role="tab" href="#overview">
                        {__('kundenfeld')}
                    </a>
                </li>
                <li class="nav-items">
                    <a class="nav-link {if $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#config">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="overview" class="tab-pane fade{if $cTab === '' || $cTab === 'uebersicht'} active show{/if}">
                <form name="kundenfeld" method="post" action="kundenfeld.php">
                    {$jtl_token}
                    <input type="hidden" name="kundenfelder" value="1">
                    <input name="tab" type="hidden" value="uebersicht">
                    {if isset($oKundenfeld->kKundenfeld) && $oKundenfeld->kKundenfeld > 0}
                        {assign var=cfEdit value=true}
                        <input type="hidden" name="kKundenfeld" value="{$oKundenfeld->kKundenfeld}">
                    {elseif isset($kKundenfeld) && $kKundenfeld > 0}
                        {assign var=cfEdit value=true}
                        <input type="hidden" name="kKundenfeld" value="{$kKundenfeld}">
                    {else}
                        {assign var=cfEdit value=false}
                    {/if}
                    <div>
                        <div class="subheading1">{if isset($oKundenfeld->kKundenfeld) && $oKundenfeld->kKundenfeld > 0}{__('headingKundenfeldEdit')}{else}{__('headingKundenfeldCreate')}{/if}</div>
                        <hr class="mb-3">
                        <div class="table-responsive">
                            <table class="table list table-borderless table-align-top" id="formtable">
                                <tr>
                                    <td><label for="cName">{__('kundenfeldName')}</label></td>
                                    <td>
                                        <input id="cName" name="cName" type="text" class="{if isset($xPlausiVar_arr.cName)}fieldfillout{/if} form-control" value="{if isset($xPostVar_arr.cName)}{$xPostVar_arr.cName}{elseif isset($oKundenfeld->cName)}{$oKundenfeld->cName}{/if}" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="cWawi">{__('kundenfeldWawi')}</label></td>
                                    <td>
                                        <input id="cWawi" name="cWawi" type="text" class="{if isset($xPlausiVar_arr.cWawi)}fieldfillout{/if} form-control"{if $cfEdit} readonly="readonly"{/if} value="{if isset($xPostVar_arr.cWawi)}{$xPostVar_arr.cWawi}{elseif isset($oKundenfeld->cWawi)}{$oKundenfeld->cWawi}{/if}" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="nSort">{__('sorting')}</label></td>
                                    <td>
                                        {if !empty($nHighestSortValue)}
                                            {assign var=nNextHighestSort value=$nHighestSortValue|intval + $nHighestSortDiff|intval}
                                            <input id="nSort" name="nSort" type="text" class="{if isset($xPlausiVar_arr.nSort)}fieldfillout{/if} form-control" value="{if isset($xPostVar_arr.nSort)}{$xPostVar_arr.nSort}{elseif isset($oKundenfeld->nSort)}{$oKundenfeld->nSort}{else}{$nNextHighestSort}{/if}"/>
                                        {else}
                                            <input id="nSort" name="nSort" type="text" class="{if isset($xPlausiVar_arr.nSort)}fieldfillout{/if} form-control" value="{if isset($xPostVar_arr.nSort)}{$xPostVar_arr.nSort}{elseif isset($oKundenfeld->nSort)}{$oKundenfeld->nSort}{/if}" placeholder="{__('kundenfeldSortDesc')}"/>
                                        {/if}
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="nPflicht">{__('kundenfeldPflicht')}</label></td>
                                    <td>
                                        <select id="nPflicht" name="nPflicht" class="{if isset($xPlausiVar_arr.nPflicht)} fieldfillout {/if}custom-select">
                                            <option value="1"{if (isset($xPostVar_arr.nPflicht) && $xPostVar_arr.nPflicht == 1) || (isset($oKundenfeld->nPflicht) && $oKundenfeld->nPflicht == 1)} selected{/if}>
                                                {__('yes')}
                                            </option>
                                            <option value="0"{if (isset($xPostVar_arr.nPflicht) && $xPostVar_arr.nPflicht == 0) || (isset($oKundenfeld->nPflicht) && $oKundenfeld->nPflicht == 0)} selected{/if}>
                                                {__('no')}
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="nEdit">{__('kundenfeldEditable')}</label></td>
                                    <td>
                                        <select id="nEdit" name="nEdit" class="{if isset($xPlausiVar_arr.nEdit)} fieldfillout{/if} custom-select">
                                            <option value="1"{if (isset($xPostVar_arr.nEdit) && $xPostVar_arr.nEdit == 1) || (isset($oKundenfeld->nEditierbar) && $oKundenfeld->nEditierbar == 1)} selected{/if}>
                                                {__('yes')}
                                            </option>
                                            <option value="0"{if (isset($xPostVar_arr.nEdit) && $xPostVar_arr.nEdit == 0) || (isset($oKundenfeld->nEditierbar) && $oKundenfeld->nEditierbar == 0)} selected{/if}>
                                                {__('no')}
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="cTyp">{__('kundenfeldTyp')}</label></td>
                                    <td>
                                        <select id="cTyp" name="cTyp" onchange="selectCheck(this);" class="{if isset($xPlausiVar_arr.cTyp)} fieldfillout{/if} custom-select">
                                            <option value="text"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'text') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'text')} selected{/if}>
                                                {__('text')}
                                            </option>
                                            <option value="zahl"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'zahl') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'zahl')} selected{/if}>
                                                {__('number')}
                                            </option>
                                            <option value="datum"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'datum') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'datum')} selected{/if}>
                                                {__('date')}
                                            </option>
                                            <option value="auswahl"{if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'auswahl') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'auswahl')} selected{/if}>
                                                {__('selection')}
                                            </option>
                                        </select>
                                        {if (isset($xPostVar_arr.cTyp) && $xPostVar_arr.cTyp === 'auswahl') || (isset($oKundenfeld->cTyp) && $oKundenfeld->cTyp === 'auswahl')}
                                            <div class="kundenfeld_wert">
                                                <button name="button" type="button" class="btn btn-primary add" value="Wert hinzufügen" onclick="addKundenfeldWert()"><i class="fal fa-plus-square-o"></i> {__('addValue')}</button>
                                            </div>
                                        {/if}
                                    </td>
                                </tr>
                                {if isset($oKundenfeld->oKundenfeldWert_arr) && $oKundenfeld->oKundenfeldWert_arr|@count > 0}
                                    {foreach name=kundenfeldwerte from=$oKundenfeld->oKundenfeldWert_arr key=key item=oKundenfeldWert}
                                        {assign var=i value=$key+1}
                                        {assign var=j value=$key+6}
                                        <tr class="kundenfeld_wert">
                                            <td class="kundenfeld_wert_label">{__('value')} {$i}:</td>
                                            <td class="row">
                                                <div class="col-lg-3 jtl-list-group">
                                                    <input name="cfValues[{$key}][cWert]" type="text" class="field form-control" value="{$oKundenfeldWert->cWert}" />
                                                </div>
                                                <div class="col-lg-2 jtl-list-group">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">{__('sortShort')}.</span>
                                                        <input name="cfValues[{$key}][nSort]" type="text" class="field form-control" value="{$oKundenfeldWert->nSort}" />
                                                    </div>
                                                </div>
                                                <div class="btn-group">
                                                    <button name="delete" type="button" class="btn btn-danger" value="Entfernen" onclick="delKundenfeldWert(this)"><i class="fas fa-trash-alt"></i> {__('remove')}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {elseif isset($xPostVar_arr.cfValues) && $xPostVar_arr.cfValues|@count > 0}
                                    {foreach name=kundenfeldwerte from=$xPostVar_arr.cfValues key=key item=cKundenfeldWert}
                                        {assign var=i value=$key+1}
                                        {assign var=j value=$key+6}
                                        <tr class="kundenfeld_wert">
                                            <td class="kundenfeld_wert_label">{__('value')} {$i}:</td>
                                            <td class="row">
                                                <div class="col-lg-3 jtl-list-group">
                                                    <input name="cfValues[{$key}][cWert]" type="text" class="field form-control" value="{$cKundenfeldWert.cWert}" />
                                                </div>
                                                <div class="col-lg-2 jtl-list-group">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">{__('sortShort')}.</span>
                                                        <input name="cfValues[{$key}][nSort]" type="text" class="field form-control" value="{$cKundenfeldWert.nSort}" />
                                                    </div>
                                                </div>
                                                <div class="btn-group">
                                                    <button name="delete" type="button" class="btn btn-danger" value="Entfernen" onclick="delKundenfeldWert(this)"><i class="fas fa-trash-alt"></i> {__('remove')}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button name="speichern" type="submit" class="btn btn-primary btn-block" value="{__('save')}">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="mt-4">
                    <div class="subheading1">{__('kundenfeldExistingDesc')}</div>
                    <hr class="mb-3">
                    <div>
                    {if isset($oKundenfeld_arr) && $oKundenfeld_arr|@count > 0}
                        <form method="post" action="kundenfeld.php">
                            {$jtl_token}
                            <input name="kundenfelder" type="hidden" value="1">
                            <input name="tab" type="hidden" value="uebersicht">
                            <div class="table-responsive">
                                <table class="table table-striped table-align-top">
                                    <thead>
                                    <tr>
                                        <th class="check"></th>
                                        <th class="text-left">{__('kundenfeldNameShort')}</th>
                                        <th class="text-left">{__('kundenfeldWawiShort')}</th>
                                        <th class="text-left">{__('kundenfeldTyp')}</th>
                                        <th class="text-left">{__('values')}</th>
                                        <th class="th-6 text-center">{__('headingKundenfeldEdit')}</th>
                                        <th class="th-7">{__('sorting')}</th>
                                        <th class="th-8 min-w-sm"</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oKundenfeld_arr as $oKundenfeld}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kKundenfeld[]" type="checkbox" value="{$oKundenfeld->kKundenfeld}" id="check-{$oKundenfeld->kKundenfeld}" />
                                                    <label class="custom-control-label" for="check-{$oKundenfeld->kKundenfeld}"></label>
                                                </div>
                                            </td>
                                            <td><label for="check-{$oKundenfeld->kKundenfeld}">{$oKundenfeld->cName}{if $oKundenfeld->nPflicht == 1} *{/if}</label></td>
                                            <td>{$oKundenfeld->cWawi}</td>
                                            <td>{$oKundenfeld->cTyp}</td>
                                            <td>
                                                {if isset($oKundenfeld->oKundenfeldWert_arr)}
                                                    {foreach $oKundenfeld->oKundenfeldWert_arr as $oKundenfeldWert}
                                                        {$oKundenfeldWert->cWert}{if !$oKundenfeldWert@last}, {/if}
                                                    {/foreach}
                                                {/if}
                                            </td>
                                            <td class="text-center">{if $oKundenfeld->nEditierbar == 1}{__('yes')}{else}{__('no')}{/if}</td>
                                            <td class="text-center">
                                                <div class="input-group form-counter min-w-sm">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                                            <span class="fas fa-minus"></span>
                                                        </button>
                                                    </div>
                                                    <input class="form-control" name="nSort_{$oKundenfeld->kKundenfeld}" type="number" value="{$oKundenfeld->nSort}"/>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                                            <span class="fas fa-plus"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="kundenfeld.php?a=edit&kKundenfeld={$oKundenfeld->kKundenfeld}&tab=uebersicht&token={$smarty.session.jtl_token}"
                                                       class="btn btn-link px-2" title="{__('modify')}"
                                                       data-toggle="tooltip">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-edit"></span>
                                                            <span class="fas fa-edit"></span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">{__('kundenfeldPflichtDesc')}</div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger btn-block mb-2">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="aktualisieren" type="submit" value="{__('update')}" class="btn btn-primary btn-block">
                                            <i class="fa fa-refresh"></i> {__('update')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    {else}
                        <div class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noDataAvailable')}</div>
                    {/if}
                    </div>
                </div>
            </div>
            <div id="config" class="tab-pane fade{if $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='kundenfeld.php' buttonCaption=__('save') title=__('settings') tab='einstellungen'}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('button[name="loeschen"]').on('click', function (e) {
        var checkedCount = $('input[name="kKundenfeld[]"]').filter(':checked').length;
        if (checkedCount === 0) {
            alert('{__('errorChooseField')}');
            e.preventDefault();

            return false;
        }

        if (!confirm('{__('sureDeleteSelected')}')) {
            e.preventDefault();

            return false;
        }
    });
    {if isset($oKundenfeld->cTyp)}
    $('form[name="kundenfeld"').on('submit', function (e) {
        if ('{$oKundenfeld->cTyp}' !== $('#cTyp').val()) {
            if (!confirm('{__('infoChangeFieldType')}')) {
                e.preventDefault();

                return false;
            }
        }

        return true;
    });
    {/if}
</script>
{include file='tpl_inc/footer.tpl'}
