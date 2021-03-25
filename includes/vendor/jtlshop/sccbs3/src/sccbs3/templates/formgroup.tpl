{*form-group will render a <fieldset> with <legend> if the label-for prop is not set.*}
{*If an input ID is provided to the label-for prop, then a <div> with <label> will be rendered.*}
{assign var=id value=$params.id->getValue()|default:uniqid()}
{assign var=breakPoint value=$params.breakpoint->getValue()}
{if $params['label-for']->hasValue()}
    <div id="{$id}" aria-labelledby="form-group-label-{$id}" class="form-group"
         {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
            {if $params.itemscope->getValue() === true}itemscope {/if}
            {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
            {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
         role="{if $params.role->hasValue()}{$params.role->getValue()}{else}group{/if}"
            {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
            {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
    >
        {if $params.horizontal->getValue() === true}
            <div class="form-row">
                <label id="form-group-label-{$id}" for="{$params['label-for']->getValue()}" class="col-form-label pt-0 col-{$breakPoint}-{$params['label-cols']->getValue()}{if $params['label-size']->hasValue()} col-form-label-{$params['label-size']->getValue()}{/if}">
                    {$params.label->getValue()}
                </label>
                <div class="col-{$breakPoint}-{12 - $params['label-cols']->getValue()}">
                    {$blockContent}
                    {if $params.description->hasValue()}
                        <small id="form-group-description-{$id}" class="form-text text-muted">{$params.description->getValue()}</small>
                    {/if}
                </div>
            </div>
        {else}
            <div class="form-group">
                {if $params.description->hasValue()}
                    <small id="form-group-description-{$id}" class="form-text text-muted">{$params.description->getValue()}</small>
                {/if}
                <label id="form-group-label-{$id}" for="{$params['label-for']->getValue()}" class="col-form-label pt-0{if $params['label-size']->hasValue()} col-form-label-{$params['label-size']->getValue()}{/if}">
                    {$params.label->getValue()}
                </label>
                {$blockContent}
            </div>
        {/if}
    </div>
{else}
    <fieldset class="form-group {$params.class->getValue()}"
              id="{$id}"
              {if $params.label->hasValue()}aria-labelledby="form-group-label-{$id}" {/if}
            {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
            {if $params.itemscope->getValue() === true}itemscope {/if}
            {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
            {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
            {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
            {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
            {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
    >
        {if $params.horizontal->getValue() === true}
        <div class="form-row">
            {/if}
            {if $params.label->hasValue()}
                <legend
                        id="form-group-label-{$id}"
                        class="col-form-label{if $params.horizontal->getValue() === true} col-{$breakPoint}-{$params['label-cols']->getValue()}{/if}{if $params['label-size']->hasValue()} col-form-label-{$params['label-size']->getValue()}{/if} pt-0">
                    {$params.label->getValue()}
                </legend>
            {/if}
            <div{if $params.horizontal->getValue() === true} class="col-{$breakPoint}-{12 - $params['label-cols']->getValue()}"{/if}>
                {$blockContent}
                {if $params.description->hasValue()}
                    <small id="form-group-description-{$id}" class="form-text text-muted">{$params.description->getValue()}</small>
                {/if}
            </div>
            {if $params.horizontal->getValue() === true}
        </div>
        {/if}
    </fieldset>
{/if}
