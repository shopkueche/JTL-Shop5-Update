<script type="text/javascript" src="{$templateBaseURL}js/sorttable.js"></script>
<script>
    $(window).on('load', function(){
        $('#submitDelete').on('click', function(){
            $('#' + $(this).data('name') + ' input[data-id="loeschen"]').trigger('click');
        });

        $('#kategorien button[data-target=".delete-modal"]').on('click', function(){
            $('.modal-title').html('{__('newsDeleteCat')}');
            $('#submitDelete').data('name', 'kategorien');

            var itemsToDelete = '';
            $('input[name="kNewsKategorie[]"]:checked').each(function(i){
                itemsToDelete += '<li class="list-group-item list-group-item-warning">' + $(this).data('name') + '</li>';
            });
            $('.delete-modal .modal-body').html('<ul class="list-group">' + itemsToDelete + '</ul>');
        });
        $('#aktiv button[data-target=".delete-modal"]').on('click', function(){
            $('.modal-title').html('{__('newsDeleteNews')}');
            $('#submitDelete').data('name', 'aktiv');
        });
        $('#inaktiv button[data-target=".delete-modal"]').on('click', function(){
            $('.modal-title').html('{__('newsDeleteComment')}');
            $('#submitDelete').data('name', 'inaktiv');
        });

        $('#category-list i.nav-toggle').on('click', function(event) {
            event.stopPropagation();
            var tr = $(this).closest('tr');
            var td = $(this).parent();
            var currentLevel = parseInt(tr.data('level')),
                state = td.hasClass('hide-toggle-on'),
                nextEl = tr.next(),
                nextLevel = parseInt(nextEl.data('level'));
            while (currentLevel < nextLevel) {
                nextEl.toggle(state);
                nextEl = nextEl.next();
                nextLevel = parseInt(nextEl.data('level'));
            }
            td.toggleClass('hide-toggle-on');
            td.find('i.fa').toggleClass('fa-caret-right fa-caret-down');
        });
    });
</script>
{include file='tpl_inc/seite_header.tpl' cTitel=__('news') cBeschreibung=__('newsDesc') cDokuURL=__('newsURL')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'inaktiv'} active{/if}" data-toggle="tab" role="tab" href="#inaktiv">
                        {__('newsCommentActivate')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'aktiv'} active{/if}" data-toggle="tab" role="tab" href="#aktiv">
                        {__('newsOverview')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'kategorien'} active{/if}" data-toggle="tab" role="tab" href="#kategorien">
                        {__('newsCatOverview')}
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
            <div id="inaktiv" class="tab-pane fade{if $cTab === '' || $cTab === 'inaktiv'} active show{/if}">
                {if $comments && $comments|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiKommentar cAnchor='inaktiv'}
                    <form method="post" action="news.php">
                        {$jtl_token}
                        <input type="hidden" name="news" value="1" />
                        <input type="hidden" name="newskommentar_freischalten" value="1" />
                        <input type="hidden" name="nd" value="1" />
                        <input type="hidden" name="tab" value="inaktiv" />
                        <div>
                            <div class="subheading1">{__('newsCommentActivate')}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table class="list table table-striped">
                                    <thead>
                                    <tr>
                                        <th class="check">&nbsp;</th>
                                        <th class="text-left">{__('visitors')}</th>
                                        <th class="text-left">{__('headline')}</th>
                                        <th class="text-left">{__('text')}</th>
                                        <th class="th-5 text-center">{__('newsDate')}</th>
                                        <th class="th-6 text-center" style="min-width: 140px;"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $comments as $comment}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" name="kNewsKommentar[]" value="{$comment->getID()}" id="comment-{$comment->getID()}" />
                                                    <label class="custom-control-label" for="comment-{$comment->getID()}"></label>
                                                </div>
                                            </td>
                                            <td class="TD2">
                                                <label for="comment-{$comment->getID()}">
                                                {*{if $comment->cVorname|strlen > 0}*}
                                                    {*{$comment->cVorname} {$comment->cNachname}*}
                                                {*{else}*}
                                                    {$comment->getName()}
                                                {*{/if}*}
                                                </label>
                                            </td>
                                            <td class="TD3">{$comment->getNewsTitle()|truncate:50:'...'}</td>
                                            <td class="TD4">{$comment->getText()|truncate:150:'...'}</td>
                                            <td class="text-center">{$comment->getDateCreatedCompat()}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="news.php?news=1&kNews={$comment->getNewsID()}&kNewsKommentar={$comment->getID()}&nkedit=1&tab=inaktiv&token={$smarty.session.jtl_token}"
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
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS1">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <input name="kommentareloeschenSubmit" type="submit" data-id="loeschen" value="{__('delete')}" class="hidden-soft">
                                        <button name="kommentareloeschenSubmit" type="button" data-toggle="modal" data-target=".delete-modal" value="{__('delete')}" class="btn btn-danger btn-block"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischalten" type="submit" value="{__('newsActivate')}" class="btn btn-primary btn-block"><i class="fa fa-thumbs-up"></i> {__('newsActivate')}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="aktiv" class="tab-pane fade{if $cTab === 'aktiv'} active show{/if}">
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiNews cAnchor='aktiv'}
                <form name="news" method="post" action="news.php">
                    {$jtl_token}
                    <input type="hidden" name="news" value="1" />
                    <input type="hidden" name="news_loeschen" value="1" />
                    <input type="hidden" name="tab" value="aktiv" />
                    <div>
                        <div class="subheading1">{__('newsOverview')}</div>
                        <hr class="mb-3">
                        <div class="table-responsive">
                            <table class="sortable list table table-striped">
                                <thead>
                                <tr>
                                    <th class="check"></th>
                                    <th class="text-left">{__('headline')}</th>
                                    <th class="text-left">{__('customerGroup')}</th>
                                    <th class="text-center">{__('newsValidation')}</th>
                                    <th class="text-center">{__('active')}</th>
                                    <th class="text-center">{__('newsComments')}</th>
                                    <th class="text-center">{__('newsCatLastUpdate')}</th>
                                    <th class="text-center" style="min-width: 100px;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $oNews_arr|@count > 0 && $oNews_arr}
                                    {foreach $oNews_arr as $oNews}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" name="kNews[]" value="{$oNews->getID()}" id="news-cb-{$oNews->getID()}" />
                                                    <label class="custom-control-label" for="news-cb-{$oNews->getID()}"></label>
                                                </div>
                                            </td>
                                            <td class="TD2"><label for="news-cb-{$oNews->getID()}">{$oNews->getTitle()}</label></td>
                                            <td class="TD4">
                                                {foreach $oNews->getCustomerGroups() as $customerGroupID}
                                                    {if $customerGroupID === -1}{__('all')}{else}{Kundengruppe::getNameByID($customerGroupID)}{/if}{if !$customerGroupID@last},{/if}
                                                {/foreach}
                                            </td>
                                            <td class="text-center">{$oNews->getDateValidFromLocalizedCompat()}</td>
                                            <td class="text-center">
                                                <i class="fal fa-{if $oNews->getIsActive()}check text-success{else}times text-danger{/if}"></i>
                                            </td>
                                            <td class="text-center">
                                                {if $oNews->getCommentCount() > 0}
                                                    <a href="news.php?news=1&nd=1&kNews={$oNews->getID()}&tab=aktiv&token={$smarty.session.jtl_token}">{$oNews->getCommentCount()}</a>
                                                {else}
                                                    {$oNews->getCommentCount()}
                                                {/if}
                                            </td>
                                            <td class="text-center">{$oNews->getDateCompat()}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="news.php?news=1&nd=1&kNews={$oNews->getID()}&tab=aktiv&token={$smarty.session.jtl_token}"
                                                       class="btn btn-link px-2"
                                                       title="{__('preview')}"
                                                       data-toggle="tooltip">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-eye"></span>
                                                            <span class="fas fa-eye"></span>
                                                        </span>
                                                    </a>
                                                    <a href="news.php?news=1&news_editieren=1&kNews={$oNews->getID()}&tab=aktiv&token={$smarty.session.jtl_token}"
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
                                {else}
                                    <tr>
                                        <td colspan="9">
                                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                                        </td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="news" value="1" />
                        <input type="hidden" name="erstellen" value="1" />
                        <input type="hidden" name="tab" value="aktiv" />
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="col-sm-6 col-xl-auto text-left">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" />
                                        <label class="custom-control-label" for="ALLMSGS2">{__('globalSelectAll')}</label>
                                    </div>
                                </div>
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <input name="loeschen" type="submit" data-id="loeschen" value="{__('delete')}" class="hidden-soft">
                                    <button name="loeschen" type="button" data-toggle="modal" data-target=".delete-modal" value="{__('delete')}" class="btn btn-danger btn-block"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button name="news_erstellen" type="submit" value="{__('newAdd')}" class="btn btn-primary btn-block"><i class="fa fa-share"></i> {__('newAdd')}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="container2">
                    <form name="erstellen" method="post" action="news.php">
                        {$jtl_token}
                    </form>
                </div>
            </div>
            <!-- #inaktiv -->
            <div id="kategorien" class="tab-pane fade{if $cTab === 'kategorien'} active show{/if}">
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiKats cAnchor='kategorien'}
                <form name="news" method="post" action="news.php">
                    {$jtl_token}
                    <input type="hidden" name="news" value="1" />
                    <input type="hidden" name="news_kategorie_loeschen" value="1" />
                    <input type="hidden" name="tab" value="kategorien" />
                    <div>
                        <div class="subheading1">{__('newsCatOverview')}</div>
                        <hr class="mb-3">
                        <div class="table-responsive">
                            <table id="category-list" class="list table table-striped">
                                <thead>
                                <tr>
                                    <th class="check"></th>
                                    <th class="text-left">{__('name')}</th>
                                    <th class=" text-center">{__('sorting')}</th>
                                    <th class="th-4 text-center">{__('active')}</th>
                                    <th class="th-5 text-center">{__('newsCatLastUpdate')}</th>
                                    <th class="th-5 text-center">&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if $oNewsKategorie_arr|@count}
                                    {foreach $oNewsKategorie_arr as $oNewsKategorie}
                                        <tr scope="row" class="tab_bg{$oNewsKategorie@iteration % 2}{if $oNewsKategorie->getLevel() > 1} hidden-soft{/if}" data-level="{$oNewsKategorie->getLevel()}">
                                            <th class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" name="kNewsKategorie[]" data-name="{$oNewsKategorie->getName()}" value="{$oNewsKategorie->getID()}" id="newscat-{$oNewsKategorie->getID()}" />
                                                    <label class="custom-control-label" for="newscat-{$oNewsKategorie->getID()}"></label>
                                                </div>
                                            </th>
                                            <td class="TD2{if $oNewsKategorie->getLevel() === 1} hide-toggle-on{/if}" data-name="category">
                                                <i class="fa fa-caret-right nav-toggle{if $oNewsKategorie->getChildren()->count() === 0} hidden{/if} cursor-pointer"></i>
                                                <label for="newscat-{$oNewsKategorie->getID()}">{$oNewsKategorie->getName()|default:'???'}</label>
                                            </td>
                                            <td class="text-center">{$oNewsKategorie->getSort()}</td>
                                            <td class="text-center">
                                                <i class="fal fa-{if $oNewsKategorie->getIsActive()}check text-success{else}times text-danger{/if}"></i>
                                            </td>
                                            <td class="text-center">{$oNewsKategorie->getDateLastModified()->format('d.m.Y H:i')}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="news.php?news=1&newskategorie_editieren=1&kNewsKategorie={$oNewsKategorie->getID()}&tab=kategorien&token={$smarty.session.jtl_token}"
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
                                        {include 'tpl_inc/newscategories_recursive.tpl' children=$oNewsKategorie->getChildren() level=$oNewsKategorie->getLevel()}
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="6">
                                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                                        </td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="news" value="1" />
                        <input type="hidden" name="erstellen" value="1" />
                        <input type="hidden" name="tab" value="kategorien" />
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="col-sm-6 col-xl-auto text-left">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" />
                                        <label class="custom-control-label" for="ALLMSGS3">{__('globalSelectAll')}</label>
                                    </div>
                                </div>
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <input name="loeschen" type="submit" data-id="loeschen" value="{__('delete')}" class="hidden-soft">
                                    <button name="loeschen" type="button" data-toggle="modal" data-target=".delete-modal" value="{__('delete')}" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash-alt"></i> {__('delete')}
                                    </button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button name="news_kategorie_erstellen" type="submit" value="{__('newsCatCreate')}" class="btn btn-primary btn-block">
                                        <i class="fa fa-share"></i> {__('newsCatCreate')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div id="einstellungen" class="tab-pane fade{if $cTab === 'einstellungen'} active show{/if}">
                <form name="einstellen" method="post" action="news.php">
                    {$jtl_token}
                    <input type="hidden" name="einstellungen" value="1" />
                    <input type="hidden" name="tab" value="einstellungen" />
                    <input type="hidden" name="news" value="1" />

                    <div class="settings">
                        <div class="subheading1">
                            {__('settings')}
                            <hr class="mb-3">
                        </div>
                        <div>
                            {foreach $oConfig_arr as $oConfig}
                                {if $oConfig->cConf === 'Y'}
                                    <div class="form-group form-row align-items-center mb-5 mb-md-3">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$oConfig->cWertName}">{$oConfig->cName}:</label>
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
                                                        class="selectpicker custom-select"
                                                        data-selected-text-format="count > 2"
                                                        data-size="7"
                                                        data-actions-box="true">
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
                                                    <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                                            <span class="fas fa-plus"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            {else}
                                                <input class="form-control" type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                                            {/if}
                                        </div>
                                        {if $oConfig->cBeschreibung}
                                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}</div>
                                        {/if}
                                    </div>
                                {/if}
                            {/foreach}

                            {foreach $oNewsMonatsPraefix_arr as $oNewsMonatsPraefix}
                                <div class="form-group form-row align-items-center mb-5 mb-md-3">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="praefix_{$oNewsMonatsPraefix->cISOSprache}">{__('newsPraefix')} ({$oNewsMonatsPraefix->name})</label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        <input type="text" class="form-control" id="praefix_{$oNewsMonatsPraefix->cISOSprache}" name="praefix_{$oNewsMonatsPraefix->cISOSprache}" value="{$oNewsMonatsPraefix->cPraefix}" tabindex="1" />
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal delete-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{__('deleteComment')}</h2>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <p>{__('wantToConfirm')}</p>
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">
                            {__('cancelWithIcon')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="button" id="submitDelete" data-name="" class="btn btn-danger">{__('delete')}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
