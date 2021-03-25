{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('suchspecialoverlay') cBeschreibung=__('suchspecialoverlayDesc') cDokuURL=__('suchspecialoverlayUrl')}
<div id="content">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    {include file='tpl_inc/language_switcher.tpl' action='suchspecialoverlay.php'}
                </div>
                <div class="col-md-auto">
                    <form name="suchspecialoverlay" method="post" action="suchspecialoverlay.php" class="inline_block">
                        {$jtl_token}
                        <div class="form-row">
                            <label class="col-sm-auto col-form-label" for="{__('suchspecial')}">{__('suchspecial')}:</label>
                            <input type="hidden" name="suchspecialoverlay" value="1" />
                            <div class="col-sm-auto">
                                <select name="kSuchspecialOverlay" class="custom-select selectBox" id="{__('suchspecial')}" onchange="document.suchspecialoverlay.submit();">
                                    {foreach $oSuchspecialOverlay_arr as $oSuchspecialOverlayTMP}
                                        <option value="{$oSuchspecialOverlayTMP->getType()}" {if $oSuchspecialOverlayTMP->getType() == $oSuchspecialOverlay->getType()}selected{/if}>{$oSuchspecialOverlayTMP->getName()}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {if $oSuchspecialOverlay->getType() > 0}
        <form name="einstellen" method="post" action="suchspecialoverlay.php" enctype="multipart/form-data" onsubmit="checkfile(event)">
            {$jtl_token}
            <input type="hidden" name="suchspecialoverlay" value="1" />
            <input type="hidden" name="kSuchspecialOverlay" value="{$oSuchspecialOverlay->getType()}" />
            <input type="hidden" name="speicher_einstellung" value="1" />

            <div class="clearall">
                <div class="no_overflow card" id="settings">
                    <div class="card-body">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('suchspecialoverlayActive')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="nAktiv" id="nAktiv" class="custom-select combo">
                                    <option value="1"{if $oSuchspecialOverlay->getActive() == 1} selected{/if}>{__('yes')}
                                    </option>
                                    <option value="0"{if $oSuchspecialOverlay->getActive() == 0} selected{/if}>{__('no')}
                                    </option>
                                </select>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=__('suchspecialoverlayActiveDesc')}
                            </div>
                        </div>

                        <div class="form-group form-row align-items-center file-input">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cSuchspecialOverlayBild">{__('suchspecialoverlayFileName')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {include file='tpl_inc/fileupload.tpl'
                                    fileID='cSuchspecialOverlayBild'
                                    fileShowRemove=true
                                    fileInitialPreview="[
                                            '<img src=\"{$oSuchspecialOverlay->getURL($smarty.const.IMAGE_SIZE_SM)}?rnd={$cRnd}\" class=\"mb-3\" />'
                                        ]"
                                }
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=__('suchspecialoverlayFileNameDesc')}
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nPrio">{__('suchspecialoverlayPrio')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="nPrio" name="nPrio" class="custom-select combo">
                                    <option value="-1"></option>
                                    {section name=prios loop=$nSuchspecialOverlayAnzahl start=1 step=1}
                                        <option value="{$smarty.section.prios.index}"{if $smarty.section.prios.index == $oSuchspecialOverlay->getPriority()} selected{/if}>{$smarty.section.prios.index}</option>
                                    {/section}
                                </select>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=__('suchspecialoverlayPrioDesc')}
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nTransparenz">{__('transparency')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="nTransparenz" class="custom-select combo" id="nTransparenz">
                                    {section name=transparenz loop=101 start=0 step=1}
                                        <option value="{$smarty.section.transparenz.index}"{if $smarty.section.transparenz.index == $oSuchspecialOverlay->getTransparance()} selected{/if}>{$smarty.section.transparenz.index}</option>
                                    {/section}
                                </select>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=__('suchspecialoverlayClarityDesc')}
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nGroesse">{__('suchspecialoverlaySize')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <div class="input-group form-counter">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                            <span class="fas fa-minus"></span>
                                        </button>
                                    </div>
                                    <input id="nGroesse" class="form-control" name="nGroesse" type="number" value="{$oSuchspecialOverlay->getSize()}" />
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                            <span class="fas fa-plus"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=__('suchspecialoverlaySizeDesc')}
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nPosition">{__('position')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="nPosition" id="nPosition" class="combo custom-select {if !empty($isDeprecated)} disabled="disabled"{/if}>
                                    <option value="1"{if $oSuchspecialOverlay->getPosition() === 1} selected{/if}>
                                        {__('topLeft')}
                                    </option>
                                    <option value="2"{if $oSuchspecialOverlay->getPosition() === 2} selected{/if}>
                                        {__('top')}
                                    </option>
                                    <option value="3"{if $oSuchspecialOverlay->getPosition() === 3} selected{/if}>
                                        {__('topRight')}
                                    </option>
                                    <option value="4"{if $oSuchspecialOverlay->getPosition() === 4} selected{/if}>
                                        {__('right')}
                                    </option>
                                    <option value="5"{if $oSuchspecialOverlay->getPosition() === 5} selected{/if}>
                                        {__('bottomRight')}
                                    </option>
                                    <option value="6"{if $oSuchspecialOverlay->getPosition() === 6} selected{/if}>
                                        {__('bottom')}
                                    </option>
                                    <option value="7"{if $oSuchspecialOverlay->getPosition() === 7} selected{/if}>
                                        {__('bottomLeft')}
                                    </option>
                                    <option value="8"{if $oSuchspecialOverlay->getPosition() === 8} selected{/if}>
                                        {__('left')}
                                    </option>
                                    <option value="9"{if $oSuchspecialOverlay->getPosition() === 9} selected{/if}>
                                        {__('centered')}
                                    </option>
                                </select>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=__('suchspecialoverlayPositionDesc')}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer save-wrapper">
                        <div class="submit">
                            <button type="submit" value="{__('save')}" class="btn btn-primary">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {/if}
</div>
<script type="text/javascript">
    {literal}
    var file2large = false;

    function checkfile(e){
        e.preventDefault();
        if (!file2large){
            document.einstellen.submit();
        }
    }

    $(document).ready(function () {
        $('form #cSuchspecialOverlayBild').on('change', function(e){
            $('form div.alert').slideUp();
            var filesize= this.files[0].size;
            {/literal}
            var maxsize = {$nMaxFileSize};
            {literal}
            if (filesize >= maxsize) {
                $('.input-group.file-input').after('<div class="alert alert-danger"><i class="fal fa-exclamation-triangle"></i>{/literal}{__('errorUploadSizeLimit')}{literal}</div>').slideDown();
                file2large = true;
            } else {
                $('form div.alert').slideUp();
                file2large = false;
            }
        });
    });
    {/literal}
</script>
{include file='tpl_inc/footer.tpl'}
