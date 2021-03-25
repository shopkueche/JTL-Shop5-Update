{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('freischalten') cBeschreibung=__('freischaltenDesc') cDokuURL=__('freischaltenURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6 col-xl-auto mb-sm-0 mb-3">
                    {include file='tpl_inc/language_switcher.tpl' id='formSprachwechselSelect' action='freischalten.php"'}
                </div>
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <form name="suche" method="post" action="freischalten.php">
                        <div class="row">
                            {$jtl_token}
                            <div class="col-sm-6 col-xl-auto mb-sm-0 mb-3">
                                <div class="form-row">
                                    <label class="col-sm-auto col-form-label" for="search_type">{__('freischaltenSearchType')}:</label>
                                    <span class="col-sm-auto">
                                        <select class="custom-select" name="cSuchTyp" id="search_type">
                                            <option value="Bewertung"{if isset($cSuchTyp) && $cSuchTyp === 'Bewertung'} selected{/if}>{__('reviews')}</option>
                                            <option value="Livesuche"{if isset($cSuchTyp) && $cSuchTyp === 'Livesuche'} selected{/if}>{__('freischaltenLivesearch')}</option>
                                            <option value="Newskommentar"{if isset($cSuchTyp) && $cSuchTyp === 'Newskommentar'} selected{/if}>{__('freischaltenNewsComments')}</option>
                                            <option value="Newsletterempfaenger"{if isset($cSuchTyp) && $cSuchTyp === 'Newsletterempfaenger'} selected{/if}>{__('freischaltenNewsletterReceiver')}</option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-auto mb-sm-0 mb-3">
                                <input type="hidden" name="Suche" value="1" />
                                <div class="form-row">
                                    <label for="search_key" class="col-sm-auto col-form-label sr-only">{__('freischaltenSearchItem')}</label>
                                    <span class="col-sm-auto mb-sm-0 mb-3">
                                        <input class="form-control" name="cSuche" type="text" value="{if isset($cSuche)}{$cSuche}{/if}"
                                               id="search_key" placeholder="{__('freischaltenSearchItem')}">
                                    </span>
                                    <div class="col-sm-auto">
                                        <button name="submitSuche" type="submit" class="btn btn-primary btn-block"><i class="fal fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'bewertungen'} active{/if}" data-toggle="tab" role="tab" href="#bewertungen">
                        {__('reviews')} <span class="badge badge-primary">{$oPagiBewertungen->getItemCount()}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'livesearch'} active{/if}" data-toggle="tab" role="tab" href="#livesearch">
                        {__('freischaltenLivesearch')} <span class="badge badge-primary">{$oPagiSuchanfragen->getItemCount()}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'newscomments'} active{/if}" data-toggle="tab" role="tab" href="#newscomments">
                        {__('freischaltenNewsComments')} <span class="badge badge-primary">{$oPagiNewskommentare->getItemCount()}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'newsletter'} active{/if}" data-toggle="tab" role="tab" href="#newsletter">
                        {__('freischaltenNewsletterReceiver')} <span class="badge badge-primary">{$oPagiNewsletterEmpfaenger->getItemCount()}</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="bewertungen" class="tab-pane fade {if $cTab === '' || $cTab === 'bewertungen'} active show{/if}">
                {if $ratings|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiBewertungen cAnchor='bewertungen'}
                    <form method="post" action="freischalten.php">
                        {$jtl_token}
                        <input type="hidden" name="freischalten" value="1" />
                        <input type="hidden" name="bewertungen" value="1" />
                        <input type="hidden" name="tab" value="bewertungen" />
                        <div>
                            <div class="subheading1">{__('reviews')}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th class="check"></th>
                                        <th class="text-left">{__('product')}</th>
                                        <th class="text-left">{__('freischaltenReviewsCustomer')}</th>
                                        <th class="text-center">{__('stars')}</th>
                                        <th class="text-center">{__('freischaltenReviewsDate')}</th>
                                        <th class="text-center">{__('actions')}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $ratings as $rating}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kBewertung[]" type="checkbox" id="review-id-{$rating->kBewertung}" value="{$rating->kBewertung}" />
                                                    <label class="custom-control-label" for="review-id-{$rating->kBewertung}"></label>
                                                </div>
                                                <input type="hidden" name="kArtikel[]" value="{$rating->kArtikel}" />
                                                <input type="hidden" name="kBewertungAll[]" value="{$rating->kBewertung}" />
                                            </td>
                                            <td><a href="{$shopURL}/index.php?a={$rating->kArtikel}" target="_blank">{$rating->ArtikelName}</a></td>
                                            <td>{$rating->cName}.</td>
                                            <td class="text-center">{$rating->nSterne}</td>
                                            <td class="text-center">{$rating->Datum}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a class="btn btn-link px-2" title="{__('modify')}"
                                                       href="bewertung.php?a=editieren&kBewertung={$rating->kBewertung}&nFZ=1&token={$smarty.session.jtl_token}"
                                                       data-toggle="tooltip"
                                                    >
                                                        <span class="icon-hover">
                                                            <span class="fal fa-edit"></span>
                                                            <span class="fas fa-edit"></span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-top-0">&nbsp;</td>
                                            <td class="border-top-0" colspan="6">
                                                <strong>{$rating->cTitel}</strong>
                                                <p>{$rating->cText}</p>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS1">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="freischaltenleoschen" type="submit" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischaltensubmit" type="submit" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('unlockMarked')}
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
            <div id="livesearch" class="tab-pane fade {if $cTab === 'livesearch'} active show{/if}">
                {if $searchQueries|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiSuchanfragen cAnchor='livesearch'}
                    <div>
                        <form method="post" action="freischalten.php">
                            {$jtl_token}
                            <input type="hidden" name="freischalten" value="1" />
                            <input type="hidden" name="suchanfragen" value="1" />
                            <input type="hidden" name="tab" value="livesearch" />
                            {if isset($nSort)}
                            <input type="hidden" name="nSort" value="{$nSort}" />
                            {/if}
                            {if isset($cSuche) && isset($cSuchTyp) && $cSuche && $cSuchTyp}
                                {assign var=cSuchStr value='Suche=1&cSuche='|cat:$cSuche|cat:'&cSuchTyp='|cat:$cSuchTyp|cat:'&'}
                            {else}
                                {assign var=cSuchStr value=''}
                            {/if}
                            <div class="table-responsive">
                                <table class="list table table-striped">
                                    <thead>
                                    <tr>
                                        <th class="check">&nbsp;</th>
                                        <th class="text-left">(<a href="freischalten.php?tab=livesearch&{$cSuchStr}nSort=1{if !isset($nSort) || $nSort != 11}1{/if}&token={$smarty.session.jtl_token}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 11}Z...A{else}A...Z{/if}</a>) {__('freischaltenLivesearchSearch')}</th>
                                        <th class="text-center">(<a href="freischalten.php?tab=livesearch&{$cSuchStr}nSort=2{if !isset($nSort) || $nSort != 22}2{/if}&token={$smarty.session.jtl_token}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 22}1...9{else}9...1{/if}</a>) {__('freischaltenLivesearchCount')}</th>
                                        <th class="text-center">(<a href="freischalten.php?tab=livesearch&{$cSuchStr}nSort=3{if !isset($nSort) || $nSort != 33}3{/if}&token={$smarty.session.jtl_token}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 33}0...1{else}1...0{/if}</a>) {__('freischaltenLivesearchHits')}</th>
                                        <th class="text-center">{__('freischaltenLiveseachDate')}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $searchQueries as $query}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kSuchanfrage[]" type="checkbox" id="search-request-id-{$query->kSuchanfrage}" value="{$query->kSuchanfrage}" />
                                                    <label class="custom-control-label" for="search-request-id-{$query->kSuchanfrage}"></label>
                                                </div>
                                            </td>
                                            <td class="text-left">{$query->cSuche}</td>
                                            <td class="text-center">{$query->nAnzahlGesuche}</td>
                                            <td class="text-center">{$query->nAnzahlTreffer}</td>
                                            <td class="text-center">{$query->dZuletztGesucht_de}</td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input"  name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS2">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <div class="input-group" data-toggle="tooltip" data-placement="bottom" title='{__('freischaltenMappingDesc')}'>
                                            <span class="input-group-addon d-none d-md-block">
                                                <label for="cMapping">{__('linkMarked')}:</label>
                                            </span>
                                            <input class="form-control" name="cMapping" id="cMapping" type="text" value="" />
                                            <span class="input-group-btn ml-1">
                                                <button name="submitMapping" type="submit" value="Verknüpfen" class="btn btn-primary">{__('linkVerb')}</button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="freischaltenleoschen" type="submit" value="Markierte löschen" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischaltensubmit" type="submit" value="Markierte freischalten" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('unlockMarked')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="newscomments" class="tab-pane fade {if $cTab === 'newscomments'} active show{/if}">
                {if $comments|@count > 0 && $comments}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiNewskommentare cAnchor='newscomments'}
                    <div>
                        <form method="post" action="freischalten.php">
                            {$jtl_token}
                            <input type="hidden" name="freischalten" value="1" />
                            <input type="hidden" name="newskommentare" value="1" />
                            <input type="hidden" name="tab" value="newscomments" />
                            <div class="table-responsive">
                                <table class="list table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="check">&nbsp;</th>
                                            <th class="text-left">{__('visitor')}</th>
                                            <th class="text-left">{__('text')}</th>
                                            <th class="text-center">{__('freischaltenNewsCommentsDate')}</th>
                                            <th class="text-center">{__('actions')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $comments as $comment}
                                            <tr>
                                                <td class="check">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" type="checkbox" name="kNewsKommentar[]" id="ncid-{$comment->kNewsKommentar}" value="{$comment->kNewsKommentar}" />
                                                        <label class="custom-control-label" for="ncid-{$comment->kNewsKommentar}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label for="ncid-{$comment->kNewsKommentar}">
                                                        {if $comment->cVorname|strlen > 0}
                                                            {$comment->cVorname} {$comment->cNachname}
                                                        {else}
                                                            {$comment->cName}
                                                        {/if}
                                                    </label>
                                                </td>
                                                <td>{$comment->cBetreff|truncate:50:'...'}</td>
                                                <td class="text-center">{$comment->dErstellt_de}</td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a class="btn btn-link px-2" title="{__('modify')}"
                                                           href="news.php?news=1&kNews={$comment->kNews}&kNewsKommentar={$comment->kNewsKommentar}&nkedit=1&nFZ=1&token={$smarty.session.jtl_token}"
                                                           data-toggle="tooltip"
                                                        >
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
                            <div class="save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS4">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="freischaltenleoschen" type="submit" value="Markierte löschen" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischaltensubmit" type="submit" value="Markierte freischalten" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('unlockMarked')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="newsletter" class="tab-pane fade {if $cTab === 'newsletter'} active show{/if}">
                {if $recipients|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiNewsletterEmpfaenger cAnchor='newsletter'}
                    <div>
                        <form method="post" action="freischalten.php">
                            {$jtl_token}
                            <input type="hidden" name="freischalten" value="1" />
                            <input type="hidden" name="newsletterempfaenger" value="1" />
                            <input type="hidden" name="tab" value="newsletter" />
                            {if isset($nSort)}
                                <input type="hidden" name="nSort" value="{$nSort}" />
                            {/if}
                            <div class="table-responsive">
                                <table class="list table">
                                    <thead>
                                        <tr>
                                            <th class="check">&nbsp;</th>
                                            <th class="text-left">{__('email')}</th>
                                            <th class="text-left">{__('firstName')}</th>
                                            <th class="text-left">{__('lastName')}</th>
                                            <th class="text-center">(<a href="freischalten.php?tab=newsletter&{$cSuchStr}nSort=4{if !isset($nSort) || $nSort != 44}4{/if}&token={$smarty.session.jtl_token}">{if !isset($nSort) || $nSort != 44}{__('old')}...{__('new')}{elseif isset($nSort) && $nSort == 44}{__('new')}...{__('old')}{/if}</a>) {__('date')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $recipients as $recipient}
                                            <tr>
                                                <td class="check">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" type="checkbox" name="kNewsletterEmpfaenger[]" id="newsletter-recipient-id-{$recipient->kNewsletterEmpfaenger}" value="{$recipient->kNewsletterEmpfaenger}" />
                                                        <label class="custom-control-label" for="newsletter-recipient-id-{$recipient->kNewsletterEmpfaenger}"></label>
                                                    </div>
                                                </td>
                                                <td>{$recipient->cEmail}</td>
                                                <td>{$recipient->cVorname}</td>
                                                <td>{$recipient->cNachname}</td>
                                                <td class="text-center">{$recipient->dEingetragen_de}</td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS5" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS5">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="freischaltenleoschen" type="submit" value="Markierte löschen" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischaltensubmit" type="submit" value="Markierte freischalten" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('unlockMarked')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
