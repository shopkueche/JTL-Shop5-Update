<div class="content-header">
    <div class="row">
        <div class="col">
            <h1 class="content-header-headline {if isset($cBeschreibung) && $cBeschreibung|@strlen == 0}nospacing{/if}">{if $cTitel|@strlen > 0}{$cTitel}{else}{__('unknown')}{/if}</h1>
        </div>
        <div class="col-auto ml-auto">
            {if $wizardDone}
                <a href="favs.php" class="btn btn-link btn-lg" data-toggle="tooltip" data-container="body" data-placement="left" title="{__('addToFavourites')}" id="fav-add">
                    <span class="fal fa-star"></span>
                </a>
            {/if}
            {if isset($cDokuURL) && $cDokuURL|@strlen > 0}
                <a href="{$cDokuURL}" target="_blank" class="btn btn-link btn-lg" data-toggle="tooltip"
                   data-container="body" data-placement="left" title="{__('goToDocu')}">
                    <span class="fal fa-map-signs"></span>
                </a>
            {/if}
        </div>
    </div>
    {if isset($cBeschreibung) && $cBeschreibung|@strlen > 0}
        <div class="description {if isset($cClass)}{$cClass}{/if}">
            {if isset($onClick)}<a href="#" onclick="{$onClick}">{/if}{$cBeschreibung}{if isset($onClick)}</a>{/if}
        </div>
    {/if}
    {if isset($pluginMeta)}
        <p><strong>{__('pluginAuthor')}:</strong> {$pluginMeta->getAuthor()}</p>
        <p><strong>{__('pluginHomepage')}:</strong> <a href="{$pluginMeta->getURL()}" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> {__($pluginMeta->getURL())}</a></p>
        <p><strong>{__('pluginVersion')}:</strong> {$pluginMeta->getVersion()}</p>
        <p><strong>{__('description')}:</strong> {__($pluginMeta->getDescription())}</p>
    {/if}
</div>
