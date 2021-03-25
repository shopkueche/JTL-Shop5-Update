{include file='tpl_inc/seite_header.tpl' cTitel=__('preview') cBeschreibung=__('newsletterdesc')}
<div id="content">
    <div class="card">
        <div class="card-body">
            <form method="post" action="newsletter.php">
                {$jtl_token}
                <input name="tab" type="hidden" value="newslettervorlagen" />
                <p><b>{__('subject')}</b>: {$oNewsletterVorlage->cBetreff}</p>
                <p><b>{__('newsletterdraftdate')}</b>: {$oNewsletterVorlage->Datum}</p>
                <div class="subheading1 mt-5 mb-3">{__('newsletterHtml')}:</div>
                <div style="text-align: center;">
                    <iframe src="{$cURL}" width="100%" height="500"></iframe>
                </div>
                <div class="subheading1 mt-5 mb-3">{__('newsletterText')}:</div>
                <div style="text-align: center;">
                    <textarea class="form-control" style="width: 100%; height: 300px;" readonly>{$oNewsletterVorlage->cInhaltText}</textarea></div>
                <br />
                <div class="save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-lg-auto">
                            <button class="btn btn-outline-primary btn-block" name="back" type="submit" value="{__('back')}">{__('goBack')}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
