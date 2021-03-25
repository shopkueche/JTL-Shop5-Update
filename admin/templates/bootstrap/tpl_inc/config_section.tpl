<form{if !empty($name)} name="{$name}"{/if} method="{if !empty($method)}{$method}{else}post{/if}"{if !empty($action)} action="{$action}"{/if}>
    {$jtl_token}
    <input type="hidden" name="einstellungen" value="1" />
    {if !empty($a)}
        <input type="hidden" name="a" value="{$a}" />
    {/if}
    {if !empty($tab)}
        <input type="hidden" name="tab" value="{$tab}" />
    {/if}
    <div class="settings">
        {if !empty($title)}
            <span class="subheading1">{$title}</span>
            <hr class="mb-3">
        {/if}
        <div>
            {foreach $config as $configItem}
                {if $configItem->cConf === 'Y'}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$configItem->cWertName}">
                            {$configItem->cName}:
                        </label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 {if $configItem->cInputTyp === 'number'}config-type-number{/if}">
                            {if $configItem->cInputTyp === 'selectbox'}
                                <select name="{$configItem->cWertName}" id="{$configItem->cWertName}" class="custom-select combo">
                                    {foreach $configItem->ConfWerte as $wert}
                                        <option value="{$wert->cWert}" {if $configItem->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'listbox'}
                                <select name="{$configItem->cWertName}[]"
                                        id="{$configItem->cWertName}"
                                        multiple="multiple"
                                        class="selectpicker custom-select combo"
                                        data-selected-text-format="count > 2"
                                        data-size="7">
                                {foreach $configItem->ConfWerte as $wert}
                                    <option value="{$wert->kKundengruppe}" {foreach $configItem->gesetzterWert as $gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'number'}
                                <div class="input-group form-counter">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                            <span class="fas fa-minus"></span>
                                        </button>
                                    </div>
                                    <input class="form-control"
                                           type="number"
                                           name="{$configItem->cWertName}"
                                           id="{$configItem->cWertName}"
                                           value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}"
                                           tabindex="1"
                                            {if $configItem->cWertName|strpos:'_bestandskundenguthaben' || $configItem->cWertName|strpos:'_neukundenguthaben'}
                                                onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$configItem->cWertName}', this);"
                                            {/if} />
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                            <span class="fas fa-plus"></span>
                                        </button>
                                    </div>
                                </div>
                            {elseif $configItem->cInputTyp === 'selectkdngrp'}
                                <select name="{$configItem->cWertName}[]" id="{$configItem->cWertName}" class="custom-select combo">
                                {foreach $configItem->ConfWerte as $wert}
                                    <option value="{$wert->kKundengruppe}" {foreach $configItem->gesetzterWert as $gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'pass'}
                                <input class="form-control" type="password" name="{$configItem->cWertName}" id="{$configItem->cWertName}"  value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}" />
                            {else}
                                <input class="form-control"
                                       type="text"
                                       name="{$configItem->cWertName}"
                                       id="{$configItem->cWertName}"
                                       value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}"
                                       tabindex="1"
                                        {if $configItem->cWertName|strpos:'_bestandskundenguthaben' || $configItem->cWertName|strpos:'_neukundenguthaben'}
                                            onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$configItem->cWertName}', this);"
                                        {/if} />
                            {/if}
                            {if $configItem->cWertName|strpos:'_bestandskundenguthaben' || $configItem->cWertName|strpos:'_neukundenguthaben'}
                                <span id="EinstellungAjax_{$configItem->cWertName}"></span>
                            {/if}
                        </div>
                        {if $configItem->cBeschreibung}
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=$configItem->cBeschreibung cID=$configItem->kEinstellungenConf}</div>
                        {/if}
                    </div>
                {elseif $showNonConf|default:false}
                    <div class="subheading1 mt-6">
                        {$configItem->cName}
                    </div>
                    <hr class="mb-3">
                {/if}
            {/foreach}
        </div>
        <div class="save-wrapper card-footer">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button name="speichern" type="submit" class="btn btn-primary btn-block">{if !empty($buttonCaption)}{$buttonCaption}{else}{__('saveWithIcon')}{/if}</button>
                </div>
            </div>
        </div>
    </div>
</form>