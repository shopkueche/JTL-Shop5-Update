{include file='tpl_inc/seite_header.tpl' cTitel=__('votesystem') cBeschreibung=__('votesystemDesc') cDokuURL=__('votesystemURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl' action='bewertung.php'}
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'freischalten'} active{/if}" data-toggle="tab" role="tab" href="#freischalten">
                        {__('ratingsInaktive')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'letzten50'} active{/if}" data-toggle="tab" role="tab" href="#letzten50">
                        {__('ratingLast50')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'artikelbewertung'} active{/if}" data-toggle="tab" role="tab" href="#artikelbewertung">
                        {__('ratingForProduct')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="freischalten" class="tab-pane fade {if $cTab === '' || $cTab === 'freischalten'} active show{/if}">
                {if $inactiveReviews|count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiInaktiv cAnchor='freischalten'}
                    <form method="post" action="bewertung.php">
                        {$jtl_token}
                        <input type="hidden" name="bewertung_nicht_aktiv" value="1" />
                        <input type="hidden" name="tab" value="freischalten" />
                        <div>
                            <div class="subheading1">{__('ratingsInaktive')}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table  class="table table-striped table-align-top">
                                    <thead>
                                    <tr>
                                        <th class="check">&nbsp;</th>
                                        <th class="text-left">{__('productName')}</th>
                                        <th class="text-left">{__('customerName')}</th>
                                        <th class="text-left">{__('ratingText')}</th>
                                        <th class="th-5 text-center">{__('ratingStars')}</th>
                                        <th class="th-6 text-center">{__('date')}</th>
                                        <th class="th-7 text-center">&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $inactiveReviews as $review}
                                            <tr>
                                                <td class="check">
                                                    <input type="hidden" name="kArtikel[{$review@index}]" value="{$review->kArtikel}"/>
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" name="kBewertung[{$review@index}]" type="checkbox" value="{$review->kBewertung}" id="inactive-{$review->kBewertung}" />
                                                        <label class="custom-control-label" for="inactive-{$review->kBewertung}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label for="inactive-{$review->kBewertung}">{$review->ArtikelName}</label>
                                                    &nbsp;<a href="{$shopURL}/index.php?a={$review->kArtikel}" target="_blank"><i class="fas fa fa-external-link"></i></a>
                                                </td>
                                                <td>{$review->cName}.</td>
                                                <td><b>{$review->cTitel}</b><br />{$review->cText}</td>
                                                <td class="text-center">{$review->nSterne}</td>
                                                <td class="text-center">{$review->Datum}</td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="bewertung.php?a=editieren&kBewertung={$review->kBewertung}&tab=freischalten&token={$smarty.session.jtl_token}"
                                                           class="btn btn-link px-2"
                                                           title="{__('modify')}"
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
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('delete')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="aktivieren" type="submit" value="{__('activate')}" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('activate')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="letzten50" class="tab-pane fade {if $cTab === 'letzten50'} active show{/if}">
                {if $activeReviews|count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiAktiv cAnchor='letzten50'}
                    <form name="letzten50" method="post" action="bewertung.php">
                        {$jtl_token}
                        <input type="hidden" name="bewertung_aktiv" value="1" />
                        <input type="hidden" name="tab" value="letzten50" />
                        <div>
                            <div class="subheading1">{__('ratingLast50')}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-striped table-align-top">
                                    <thead>
                                    <tr>
                                        <th class="check">&nbsp;</th>
                                        <th class="text-left">{__('productName')}</th>
                                        <th class="text-left">{__('customerName')}</th>
                                        <th class="text-left">{__('ratingText')}</th>
                                        <th class="th-5 text-center">{__('ratingStars')}</th>
                                        <th class="th-6 text-center">{__('date')}</th>
                                        <th class="th-7 text-center min-w-sm">{__('actions')}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $activeReviews as $review}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kBewertung[]" type="checkbox" value="{$review->kBewertung}" id="l50-{$review->kBewertung}">
                                                    <label class="custom-control-label" for="l50-{$review->kBewertung}"></label>
                                                </div>
                                                <input type="hidden" name="kArtikel[]" value="{$review->kArtikel}">
                                            </td>
                                            <td>
                                                <label for="l50-{$review->kBewertung}">{$review->ArtikelName}</label>
                                            </td>
                                            <td>{$review->cName}.</td>
                                            <td>
                                                <strong>{$review->cTitel}</strong><br>
                                                {$review->cText}
                                                {if !empty($review->cAntwort)}
                                                    <blockquote class="review-reply">
                                                        <strong>{__('ratingReply')}</strong><br>
                                                        {$review->cAntwort}
                                                    </blockquote>
                                                {/if}
                                            </td>
                                            <td class="text-center">{$review->nSterne}</td>
                                            <td class="text-center">{$review->Datum}</td>
                                            <td class="text-center">
                                                {if !empty($review->cAntwort)}
                                                    <a href="bewertung.php?a=delreply&kBewertung={$review->kBewertung}&tab=letzten50&token={$smarty.session.jtl_token}"
                                                       class="btn btn-link px-2"
                                                       title="{__('removeReply')}"
                                                       data-toggle="tooltip">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-trash-alt"></span>
                                                            <span class="fas fa-trash-alt"></span>
                                                        </span>
                                                    </a>
                                                {/if}
                                                <a class="btn btn-link px-2"
                                                   href="{$shopURL}/index.php?a={$review->kArtikel}"
                                                   target="_blank"
                                                   title="{__('linkItemShop')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-external-link"></span>
                                                        <span class="fas fa-external-link"></span>
                                                    </span>
                                                </a>
                                                <a href="bewertung.php?a=editieren&kBewertung={$review->kBewertung}&tab=letzten50&token={$smarty.session.jtl_token}"
                                                   class="btn btn-link px-2"
                                                   title="{__('modify')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-edit"></span>
                                                        <span class="fas fa-edit"></span>
                                                    </span>
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS3">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger btn-block"><i class="fas fa-trash-alt"></i> {__('deleteSelected')}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="artikelbewertung" class="tab-pane fade {if $cTab === 'artikelbewertung'} active show{/if}">
                <form name="artikelbewertung" method="post" action="bewertung.php">
                    <div class="mb-3">
                        {$jtl_token}
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="content">{__('ratingcArtNr')}:</label>
                            <input type="hidden" name="bewertung_aktiv" value="1" />
                            <input type="hidden" name="tab" value="artikelbewertung" />
                            <div class="col-sm-auto mb-3">
                                <input class="form-control" name="cArtNr" type="text" value="{$cArtNr|default:''}" />
                            </div>
                            <span class="col-sm-auto">
                                <button name="submitSearch" type="submit" value="{__('search')}" class="btn btn-primary btn-block mb-3">
                                    <i class="fal fa-search"></i>
                                </button>
                            </span>
                        </div>
                        {if isset($cArtNr) && $cArtNr|strlen > 0}
                            <div class="alert alert-info">{__('ratingSearchedFor')}: {$cArtNr}</div>
                        {/if}
                        {if !(isset($filteredReviews) && $filteredReviews|@count > 0)}
                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                        {/if}
                    </div>
                    {if isset($filteredReviews) && $filteredReviews|@count > 0}
                        <div>
                            <div class="subheading1">{$cArtNr}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-striped table-align-top">
                                    <thead>
                                    <tr>
                                        <th class="th-1">&nbsp;</th>
                                        <th class="text-left">{__('productName')}</th>
                                        <th class="text-left">{__('customerName')}</th>
                                        <th class="text-left">{__('ratingText')}</th>
                                        <th class="th-5 text-center">{__('ratingStars')}</th>
                                        <th class="th-6 text-center">{__('date')}</th>
                                        <th class="th-7 text-center">&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $filteredReviews as $review}
                                        <tr>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kBewertung[]" type="checkbox" value="{$review->kBewertung}" id="filtered-{$review->kBewertung}">
                                                    <label class="custom-control-label" for="filtered-{$review->kBewertung}"></label>
                                                </div>
                                                <input type="hidden" name="kArtikel[]" value="{$review->kArtikel}">
                                            </td>
                                            <td>
                                                <label for="filtered-{$review->kBewertung}">{$review->ArtikelName}</label>
                                                &nbsp;<a href="{$shopURL}/index.php?a={$review->kArtikel}" target="_blank"><i class="fas fa fa-external-link"></i></a>
                                            </td>
                                            <td>{$review->cName}.</td>
                                            <td><b>{$review->cTitel}</b><br />{$review->cText}</td>
                                            <td class="text-center">{$review->nSterne}</td>
                                            <td class="text-center">{$review->Datum}</td>
                                            <td class="text-center">
                                                <a href="bewertung.php?a=editieren&kBewertung={$review->kBewertung}&tab=artikelbewertung"
                                                   class="btn btn-link px-2"
                                                   title="{__('modify')}"
                                                   data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-edit"></span>
                                                        <span class="fas fa-edit"></span>
                                                    </span>
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS2">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="loeschen" type="submit" class="btn btn-danger btn-block"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                </form>
            </div>
            <div id="einstellungen" class="tab-pane fade {if $cTab === 'einstellungen'} active show{/if}">
                <form name="einstellen" method="post" action="bewertung.php">
                    {$jtl_token}
                    <input type="hidden" name="einstellungen" value="1" />
                    <input type="hidden" name="tab" value="einstellungen" />
                    <div class="settings">
                        <span class="subheading1">{__('settings')}</span>
                        <hr class="mb-3">
                        <div>
                            {foreach $oConfig_arr as $oConfig}
                                {if $oConfig->cConf === 'Y'}
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$oConfig->cWertName}">
                                            {$oConfig->cName}{if $oConfig->cWertName|strpos:'_guthaben'} <span id="EinstellungAjax_{$oConfig->cWertName}"></span>:{else}:{/if}
                                        </label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            {if $oConfig->cInputTyp === 'selectbox'}
                                                <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="custom-select combo">
                                                    {foreach $oConfig->ConfWerte as $wert}
                                                        <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                    {/foreach}
                                                </select>
                                            {elseif $oConfig->cInputTyp === 'listbox'}
                                                <select name="{$oConfig->cWertName}[]"
                                                        id="{$oConfig->cWertName}"
                                                        multiple="multiple"
                                                        class="selectpicker custom-select combo"
                                                        data-selected-text-format="count > 2"
                                                        data-size="5">
                                                    {foreach $oConfig->ConfWerte as $wert}
                                                        <option value="{$wert->kKundengruppe}" {foreach $oConfig->gesetzterWert as $gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                                    {/foreach}
                                                </select>
                                            {elseif $oConfig->cInputTyp === 'number'}
                                                <div class="input-group form-counter">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                                            <span class="fas fa-minus"></span>
                                                        </button>
                                                    </div>
                                                    <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1"{if $oConfig->cWertName|strpos:"_guthaben"} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$oConfig->cWertName}', this);"{/if} />
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                                            <span class="fas fa-plus"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            {else}
                                                <input class="form-control" type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1"{if $oConfig->cWertName|strpos:"_guthaben"} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$oConfig->cWertName}', this);"{/if} />
                                            {/if}
                                        </div>
                                        {if $oConfig->cBeschreibung}
                                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}</div>
                                        {/if}
                                    </div>
                                {else}
                                    {if $oConfig->cBeschreibung}
                                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                            {getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}
                                        </div>
                                    {/if}
                                {/if}
                            {/foreach}
                        </div>
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button type="submit" value="{__('save')}" class="btn btn-primary btn-block">{__('saveWithIcon')}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    {foreach $oConfig_arr as $oConfig}
        {if $oConfig->cWertName|strpos:'_guthaben'}
            ioCall(
                'getCurrencyConversion',
                [0, $('#{$oConfig->cWertName}').val(), 'EinstellungAjax_{$oConfig->cWertName}'],
                undefined,
                undefined,
                undefined,
                true
            );
        {/if}
    {/foreach}
</script>
