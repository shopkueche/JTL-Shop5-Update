{assign var=cTitel value=__('newLinkGroupTitle')}
{if $linkGroup !== null}
    {assign var=cTitel value=__('saveLinkGroup')}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel}

<div id="content">

    <form name="linkgruppe_erstellen" method="post" action="links.php">
        <div class="card">
            <div class="card-body">
                {$jtl_token}
                <input type="hidden" name="kLinkgruppe" value="{if $linkGroup !== null}{$linkGroup->getID()}{/if}" />

                <div class="settings">
                    <div class="form-group form-row align-items-center{if isset($xPlausiVar_arr.cName)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('linkGroup')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" name="cName" id="cName"  class="form-control" value="{if isset($xPostVar_arr.cName)}{$xPostVar_arr.cName}{elseif $linkGroup !== null}{$linkGroup->getGroupName()}{/if}" />
                        </div>
                    </div>

                    <div class="form-group form-row align-items-center{if isset($xPlausiVar_arr.cTemplatename)} form-error{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cTemplatename">{__('linkGroupTemplatename')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" name="cTemplatename" id="cTemplatename" class="form-control" value="{if isset($xPostVar_arr.cTemplatename)}{$xPostVar_arr.cTemplatename}{elseif $linkGroup !== null}{$linkGroup->getTemplate()}{/if}" />
                        </div>
                    </div>
                    {foreach $availableLanguages as $language}
                        {assign var=cISO value=$language->getIso()}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cName_{$cISO}">{__('showedName')} ({$language->getLocalizedName()}):</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" type="text" name="cName_{$cISO}" id="cName_{$cISO}" value="{if $linkGroup !== null}{$linkGroup->getName($language->getId())}{/if}" />
                            </div>
                        </div>
                    {/foreach}
                </div>

            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a class="btn btn-outline-primary btn-block" href="links.php">
                            {__('cancelWithIcon')}
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" class="btn btn-primary btn-block" name="action" value="save-linkgroup"><i class="fa fa-save"></i> {$cTitel}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
