{assign var=id value=$params.id->getValue()|default:uniqid()}
{$buttons = false}
{$stacked = false}
{$plain = false}
{$parentParams = $parentSmarty->getTemplateVars('pbp')|default:$parentBlockParams}

{if isset($parentParams.stacked)}
    {assign var=stacked value=$parentParams.stacked->getValue()}
{/if}
{if isset($parentParams.plain)}
    {assign var=plain value=$parentParams.plain->getValue()}
{/if}
{if isset($parentParams.buttons)}
    {assign var=buttons value=$parentParams.buttons->getValue()}
{/if}
{if $buttons === false}
    <div class="{if $plain === false}custom-control custom-checkbox{if $stacked === false} custom-control-inline{/if}{else}form-check{if $stacked === false} form-check-inline{/if}{/if}"
        {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
        {if $params.itemscope->getValue() === true}itemscope {/if}
        {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
        {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    >
        <input class="{if $plain === false}custom-control-input{else}form-check-input{/if} {$params.class->getValue()}"
            type="checkbox" id="{$id}"
            {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
            {if $params.value->hasValue()}value="{$params.value->getValue()}"{/if}
            {if $params.name->hasValue()}name="{$params.name->getValue()}"{/if}
            {if $params.disabled->getValue() === true}disabled{/if}
            {if $params.required->getValue() === true}required{/if}
            {if $params.checked->getValue() === true}checked{/if}
            {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
            {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
            {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
        >
        {if !empty($blockContent)}
            <label for="{$id}" class="{if $plain === false}custom-control-label{else}form-check-label{/if}">
                {$blockContent}
            </label>
        {/if}
    </div>
{else}
    <label class="btn btn-{$parentParams['button-variant']->getValue()} btn-{$parentParams.size->getValue()}{if $params.disabled->getValue() === true} disabled{/if}"
        {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
        {if $params.itemscope->getValue() === true}itemscope {/if}
        {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
        {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    >
        <input id="{$id}" type="checkbox"
            {if $params.value->hasValue()}value="{$params.value->getValue()}"{/if}
            {if $params.name->hasValue()}name="{$params.name->getValue()}"{/if}
            {if $params.title->hasValue()} title="{$params.title->getValue()}"{/if}
            {if $params.disabled->getValue() === true}disabled{/if}
            {if $params.required->getValue() === true}required{/if}
            {if $params.checked->getValue() === true}checked{/if}
            class="{$params.class->getValue()}"
            {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
            {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
            {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
        >
        <span><span>{$blockContent}</span></span>
    </label>
{/if}
