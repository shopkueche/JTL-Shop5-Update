{assign var=onchange value=$onchange|default:true}
{assign var=id value=$id|default:'lang-switcher'}
<form name="sprache" method="post" action="{$action|default:''}" class="inline_block">
    {$jtl_token}
    <input type="hidden" name="sprachwechsel" value="1" />
    <div class="form-row">
        <label class="col-sm-auto col-form-label" for="{$id}">{__('changeLanguage')}:</label>
        <span class="col-sm-auto">
            <select id="{$id}" name="kSprache" class="custom-select selectBox"{if $onchange} onchange="document.sprache.submit();"{/if}>
                {foreach $availableLanguages as $language}
                    <option value="{$language->getId()}" {if $language->getId() === $smarty.session.editLanguageID}{assign var=currentLanguage value=$language->getLocalizedName()}selected{/if}>{$language->getLocalizedName()}</option>
                {/foreach}
            </select>
        </span>
    </div>
</form>
