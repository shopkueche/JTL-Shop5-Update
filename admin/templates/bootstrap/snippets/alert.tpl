<div class="alert alert-{$alert->getCssType()} align-items-center"
    data-fade-out="{$alert->getFadeOut()}"
    data-key="{$alert->getKey()}"
    {if $alert->getId()}id="{$alert->getId()}"{/if}
>
    {if $alert->getIcon() === 'danger' || $alert->getIcon() === 'warning'}
        {assign var='icon' value='exclamation-triangle'}
    {else}
        {assign var='icon' value=$alert->getIcon()}
    {/if}
    <div class="row">
        <div class="col">
            {if $alert->getIcon()}<i class="fal fa-{$icon} mr-2"></i>{/if}
            {if !empty($alert->getLinkHref()) && empty($alert->getLinkText())}
                <a href="{$alert->getLinkHref()}">{$alert->getMessage()}</a>
            {elseif !empty($alert->getLinkHref()) && !empty($alert->getLinkText())}
                {$alert->getMessage()}
                <a href="{$alert->getLinkHref()}">{$alert->getLinkText()}</a>
            {else}
                {$alert->getMessage()}
            {/if}
        </div>
        <div class="col-auto ml-auto">
            {if $alert->getDismissable()}<div class="close">&times;</div>{/if}
        </div>
    </div>
</div>
