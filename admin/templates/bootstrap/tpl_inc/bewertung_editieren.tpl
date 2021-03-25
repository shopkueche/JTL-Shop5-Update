{include file='tpl_inc/seite_header.tpl' cTitel=__('bearbeiteBewertung')}
<div id="content">
    <div class="card">
        <div class="card-header">
            <div class="subheading1">{__('bearbeiteBewertung')}</div>
            <hr class="mb-n3">
        </div>
        <form name="umfrage" method="post" action="bewertung.php">
            <div class="card-body">
                {$jtl_token}
                <input type="hidden" name="bewertung_editieren" value="1" />
                <input type="hidden" name="tab" value="{$cTab}" />
                {if isset($nFZ) && $nFZ == 1}<input name="nFZ" type="hidden" value="1">{/if}
                <input type="hidden" name="kBewertung" value="{$review->getId()}" />

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="name">{__('customerName')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" id="name" name="cName" type="text" value="{$review->getName()}" />
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="title">{__('title')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" id="title" name="cTitel" type="text" value="{$review->getTitle()}" />
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="stars">{__('ratingStars')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select id="stars" name="nSterne" class="custom-select combo">
                            {for $i=1 to 5}
                                <option value="{$i}"{if $review->getStars() === $i} selected{/if}>{$i}</option>
                            {/for}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="content">{__('ratingText')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <textarea id="content" class="ckeditor" name="cText" rows="15" cols="60">{$review->getContent()}</textarea>
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="answer">{__('ratingReply')}</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <textarea id="answer" class="ckeditor" name="cAntwort" rows="15" cols="60">{$review->getAnswer()}</textarea>
                    </div>
                </div>
            </div>
            <div class="save-wrapper card-footer">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a class="btn btn-outline-primary btn-block" href="bewertung.php?tab={$cTab}">
                            {__('cancelWithIcon')}
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button name="bewertungsubmit" type="submit" value="{__('save')}" class="btn btn-primary btn-block">{__('saveWithIcon')}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
