{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('shoplogouploader') cBeschreibung=__('shoplogouploaderDesc') cDokuURL=__('shoplogouploaderURL')}
<div id="content">
    <form name="uploader" method="post" action="shoplogouploader.php" enctype="multipart/form-data">
        {$jtl_token}
        <div class="card">
            <div class="card-header">
                <span class="subheading1">{__('yourLogo')}</span>
            </div>
            <div class="card-body">
                <input type="hidden" name="upload" value="1" />
                {$allowedExtensions = ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'svg', 'webp']}
                <div class="col-xs-12">
                    {include file='tpl_inc/fileupload.tpl'
                        fileAllowedExtensions=$allowedExtensions
                        fileID='shoplogo-upload'
                        fileName='shopLogo'
                        fileUploadUrl="{$adminURL}/shoplogouploader.php?token={$smarty.session.jtl_token}"
                        fileDeleteUrl="{$adminURL}/shoplogouploader.php?token={$smarty.session.jtl_token}"
                        fileBrowseClear=true
                        fileSuccessMsg="{__('successLogoUpload')}"
                        fileErrorMsg="{__('errorLogoUpload', implode(', ', $allowedExtensions), $smarty.const.PFAD_SHOPLOGO)}"
                        fileInitialPreview="[
                                '<img src=\"{$ShopLogoURL}\" class=\"file-preview-image img-fluid\" alt=\"Logo\" title=\"Logo\" />'
                            ]"
                        fileInitialPreviewConfig="[
                                {
                                    url: '{$adminURL}/shoplogouploader.php',
                                    extra: {
                                    action: 'deleteLogo',
                                    logo: '{$ShopLogo}',
                                    jtl_token: '{$smarty.session.jtl_token}'
                                    }
                                }
                            ]"
                    }
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
