{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('newsletterMail') cBeschreibung=__('newsletterMailDesc') cDokuURL=__('newsletterURL')}
<div id="content">
    <div class="card">
        <form name="kundenimporter" method="post" action="newsletterimport.php" enctype="multipart/form-data">
            <div class="card-body">
                {$jtl_token}
                <input type="hidden" name="newsletterimport" value="1" />
                <div class="settings">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kSprache">{__('language')}:</label>
                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select name="kSprache" id="kSprache" class="custom-select combo">
                                {foreach $availableLanguages as $language}
                                    <option value="{$language->getId()}">{$language->getLocalizedName()}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="csv">{__('csvFile')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            {include file='tpl_inc/fileupload.tpl'
                                fileID='csv'
                                fileAllowedExtensions="['csv','txt']"
                                fileShowRemove=true
                                fileMaxSize=false
                                fileRequired=true
                            }
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="submit" value="{__('import')}" class="btn btn-primary btn-block">{__('import')}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
