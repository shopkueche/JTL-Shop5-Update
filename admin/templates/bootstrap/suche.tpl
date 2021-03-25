{if $standalonePage}
    {include file='tpl_inc/header.tpl'}
    {$cTitel = {__('searchResultsFor')}|sprintf:$query}
    {include file='tpl_inc/seite_header.tpl' cTitel=$cTitel}
    <div class="card">
        <div class="card-body search-page">
{/if}

{if $adminMenuItems|count}
    <div class="dropdown-header">{__('pagesMenu')}</div>
    <ul class="backend-search-section">
        {foreach $adminMenuItems as $item}
            <li class="has-icon" tabindex="-1">
                <a class="dropdown-item" href="{$item->link}">
                    <span class="title">
                        <span class="mr-2">{include file="img/icons/{$item->icon}.svg"}</span>
                        {$item->path}
                    </span>
                </a>
            </li>
        {/foreach}
    </ul>
    <div class="dropdown-divider dropdown-divider-light"></div>
{/if}
{if isset($settings)}
    <div class="dropdown-header">{__('content')}</div>
    <ul>
        {foreach $settings as $setting}
            <li>
                <a class="dropdown-item" href="{$setting->cURL}">
                    <span class="title">{__($setting->cWertName)}</span>
                    <span class="path">{$setting->cSektionsPfad}</span>
                </a>
                <ul>
                    {foreach $setting->oEinstellung_arr as $s}
                        <li tabindex="-1">
                            <a class="dropdown-item value" href="einstellungen.php?cSuche={$s->kEinstellungenConf}&einstellungen_suchen=1">
                                <span class="title">{$s->cName}
                                    {*<small>{$s->cBeschreibung}</small>*}
                                </span>
                                <span class="path">{__('settingNumberShort')}: {$s->kEinstellungenConf}</span>
                            </a>
                        </li>
                    {/foreach}
                </ul>
            </li>
        {/foreach}
    </ul>
{/if}
{if isset($shippings)}
    <div class="dropdown-divider dropdown-divider-light"></div>
    <div class="dropdown-header"><a href="versandarten.php" class="value">{__('shippingTypesOverview')}</a></div>
    <ul>
        {foreach $shippings as $shipping}
            <li class="dropdown-item is-form-submit" tabindex="-1">
                <form method="post" action="versandarten.php">
                    {$jtl_token}
                    <input type="hidden" name="edit" value="{$shipping->kVersandart}">
                    <button type="submit" class="btn btn-link p-0">{$shipping->cName}</button>
                </form>
            </li>
        {/foreach}
    </ul>
{/if}
{if isset($paymentMethods)}
    <div class="dropdown-divider dropdown-divider-light"></div>
    <div class="dropdown-header"><a href="zahlungsarten.php" class="value">{__('paymentTypesOverview')}</a></div>
    <ul>
        {foreach $paymentMethods as $paymentMethod}
            <li>
                <a href="zahlungsarten.php?kZahlungsart={$paymentMethod->kZahlungsart}&token={$smarty.session.jtl_token}" class="dropdown-item value">
                    {$paymentMethod->cName}
                </a>
            </li>
        {/foreach}
    </ul>
{/if}
{if $plugins->isNotEmpty()}
    <div class="dropdown-divider dropdown-divider-light"></div>
    <div class="dropdown-header"><a href="pluginverwaltung.php" class="value">{__('Plug-in manager')}</a></div>
    <ul>
        {foreach $plugins as $plugin}
            <li>
                <a href="plugin.php?kPlugin={$plugin->getID()}&token={$smarty.session.jtl_token}" class="dropdown-item value">
                    {$plugin->getName()}
                </a>
            </li>
        {/foreach}
    </ul>
{/if}

{if empty($adminMenuItems) && empty($settings) && empty($shippings) && empty($paymentMethods) && $plugins->isEmpty()}
    <span class="{if !$standalonePage}ml-3{/if}">{__('noSearchResult')}</span>
{/if}
{if $standalonePage}
        </div>
    </div>
    {include file='tpl_inc/footer.tpl'}
{/if}
