<div class="modal {$params.class->getValue()}" tabindex="-1" {if $params.id->hasValue()} id="{$params.id->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
    role="{if $params.role->hasValue()}{$params.role->getValue()}{else}dialog{/if}"
    {if $params.aria->hasValue()}{foreach $params.aria->getValue() as $ariaKey => $ariaVal}aria-{$ariaKey}="{$ariaVal}" {/foreach}{/if}
    {if $params.data->hasValue()}{foreach $params.data->getValue() as $dataKey => $dataVal}data-{$dataKey}="{$dataVal}" {/foreach}{/if}
>
    <div class="modal-dialog{if $params.centered->getValue() === true} modal-dialog-centered{/if}{if $params.size->hasValue()} modal-{$params.size->getValue()}{/if}" role="document">
        <div class="modal-content">
            <div class="modal-header">
                {if $params.title->hasValue()}<h5 class="modal-title">{$params.title->getValue()}</h5>{/if}
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {$blockContent}
            </div>
            {if $params.footer->hasValue()}
            <div class="modal-footer">
                {$params.footer->getValue()}
            </div>
            {/if}
        </div>
    </div>
</div>