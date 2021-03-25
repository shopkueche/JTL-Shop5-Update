{include file='tpl_inc/seite_header.tpl' cTitel=__('news') cBeschreibung=__('newsDesc')}
<div id="content">
    <div class="card">
        <div class="card-header category first clearall">
            <div class="subheading1">{$oNews->getTitle()} - {$oNews->getDate()->format('d.m.Y H:i')}</div>
            <hr class="mb-n3">
        </div>
        <div class="card-body">
            {$oNews->getContent()}

        {if $comments|@count > 0}
            <form method="post" action="news.php">
                {$jtl_token}
                <input type="hidden" name="news" value="1" />
                <input type="hidden" name="kNews" value="{$oNews->getID()}" />
                <input type="hidden" name="kommentare_loeschen" value="1" />
                {if isset($cTab)}
                    <input type="hidden" name="tab" value="{$cTab}" />
                {/if}
                {if isset($cSeite)}
                    <input type="hidden" name="s2" value="{$cSeite}" />
                {/if}
                <input type="hidden" name="nd" value="1" />
                <div class="category subheading1 mt-3">
                    {__('newsComments')}
                    <hr class="my-2">
                </div>
                {foreach $comments as $comment}
                    <div class="card">
                        <div class="card-header">
                            <div class="form-check">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input form-check-input" name="kNewsKommentar[]" type="checkbox" value="{$comment->getID()}" id="nk-{$comment->getID()}" />
                                    <label class="custom-control-label form-check-label" for="nk-{$comment->getID()}">{$comment->getName()}, {$comment->getDateCreated()->format('d.m.Y H:i')}</label>
                                    <div class="btn-group">
                                        <a href="news.php?news=1&kNews={$oNews->getID()}&kNewsKommentar={$comment->getID()}{if isset($cBackPage)}&{$cBackPage}{elseif isset($cTab)}&tab={$cTab}{/if}&nkedit=1&token={$smarty.session.jtl_token}"
                                           class="btn btn-link px-2"
                                           title="{__('modify')}"
                                           data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-edit"></span>
                                            <span class="fas fa-edit"></span>
                                        </span>
                                        </a>
                                        <a href="news.php?news=1&kNews={$oNews->getID()}&parentCommentID={$comment->getID()}{if isset($cBackPage)}&{$cBackPage}{elseif isset($cTab)}&tab={$cTab}{/if}&nkanswer=1&token={$smarty.session.jtl_token}"
                                           class="btn btn-link px-2"
                                           title="{__('answerComment')}"
                                           data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-reply"></span>
                                            <span class="fas fa-reply"></span>
                                        </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <hr class="mb-n3">
                        </div>
                        <div class="card-body">
                            {$comment->getText()}
                                {foreach $comment->getChildComments() as $childComment}
                                <div class="card ">
                                    <div class="card-header">
                                        <div class="form-check">
                                            <div class=" custom-control custom-checkbox">
                                                <input class="custom-control-input form-check-input" name="kNewsKommentar[]" type="checkbox" value="{$childComment->getID()}" id="nk-{$childComment->getID()}" />
                                                <label class="custom-control-label form-check-label" for="nk-{$childComment->getID()}">{$childComment->getName()}, {$childComment->getDateCreated()->format('d.m.Y H:i')}:</label>
                                            </div>
                                        </div>
                                        <hr class="mb-n3">
                                    </div>
                                    <div class="card-body">
                                        <div class="review-reply mt-3 ml-3">
                                            <blockquote>
                                                {$childComment->getText()}
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                                {/foreach}
                        </div>
                    </div>
                {/foreach}
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="kommentar_loeschen" type="submit" value="{__('delete')}" class="btn btn-danger btn-block">
                                <i class="fas fa-trash-alt"></i> {__('delete')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <a class="btn btn-outline-primary btn-block" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}">
                                {__('goBack')}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        {else}
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a class="btn btn-outline-primary btn-block" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}">
                            {__('goBack')}
                        </a>
                    </div>
                </div>
            </div>
        {/if}
        </div>
    </div>
</div>
