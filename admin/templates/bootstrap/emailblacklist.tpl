{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('emailblacklist') cBeschreibung=__('emailblacklistDesc') cDokuURL=__('emailblacklistURL')}
<div id="content">
    <form method="post" action="emailblacklist.php">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <input type="hidden" name="emailblacklist" value="1" />
        <div id="settings">
            {assign var=open value=false}
            {foreach $config as $configItem}
                {if $configItem->cConf === 'Y'}
                    <div class="item form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$configItem->cWertName}">{$configItem->cName}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            {if $configItem->cInputTyp === 'selectbox'}
                                <select name="{$configItem->cWertName}" id="{$configItem->cWertName}" class="custom-select combo">
                                    {foreach $configItem->ConfWerte as $wert}
                                        <option value="{$wert->cWert}" {if $configItem->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'number'}
                                <div class="input-group form-counter">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                            <span class="fas fa-minus"></span>
                                        </button>
                                    </div>
                                    <input class="form-control" type="number" name="{$configItem->cWertName}" id="{$configItem->cWertName}" value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}" tabindex="1" />
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                            <span class="fas fa-plus"></span>
                                        </button>
                                    </div>
                                </div>
                            {else}
                                <input type="text" name="{$configItem->cWertName}" id="{$configItem->cWertName}" value="{$configItem->gesetzterWert}" tabindex="1" />
                            {/if}
                        </div>
                        {if $configItem->cBeschreibung}
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=$configItem->cBeschreibung}</div>
                        {/if}
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="card">
                    {if $configItem->cName}
                        <div class="card-header">
                            <div class="subheading1">{$configItem->cName}</div>
                            <hr class="mb-n3">
                        </div>
                    {/if}
                        <div class="card-body">
                    {assign var=open value=true}
                {/if}
            {/foreach}
            {if $open}
                    </div>
                </div>
            {/if}
        </div>

        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('emailblacklistEmail')} {__('emailblacklistSeperator')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <textarea class="form-control" name="cEmail" cols="50" rows="10">{foreach $blacklist as $item}{$item->cEmail}{if !$item@last};{/if}{/foreach}</textarea>
            </div>
        </div>
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                        {__('saveWithIcon')}
                    </button>
                </div>
            </div>
        </div>
    </form>
    {if $blocked|@count > 0}
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('emailblacklistBlockedEmails')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                {foreach $blocked as $item}
                    {$item->cEmail} ({$item->dLetzterBlock})<br />
                {/foreach}
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
