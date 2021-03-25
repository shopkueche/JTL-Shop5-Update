<span data-html="true"
        data-toggle="tooltip"
        data-placement="{$placement}"
        title="{if $description !== null}{$description}{/if}{if $cID !== null && $description !== null}<hr>{/if}{if $cID !== null}<p><strong>{__('settingNumberShort')}: </strong>{$cID}</p>{/if}">
    {if $iconQuestion}
        <span class="fas fa-question-circle fa-fw"></span>
    {else}
        <span class="fas fa-info-circle fa-fw"></span>
    {/if}
</span>
