{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('My purchases') cBeschreibung=__('pageDesc') cDokuURL=__('https://www.jtl-software.de')}
{if $smarty.const.SAFE_MODE === false}
    <div id="content">
        <div id="error-placeholder" class="alert alert-danger d-none"></div>
        {include file='tpl_inc/licenses_store_connection.tpl'}
        {if $hasAuth}
            {include file='tpl_inc/licenses_bound.tpl' licenses=$licenses}
            {include file='tpl_inc/licenses_unbound.tpl' licenses=$licenses}
            {if isset($smarty.get.debug)}
                <h3>AuthToken</h3>
                <pre>{$authToken}</pre>
                <h3>Bound licenses</h3>
                <pre>{$licenses->getBound()|dump}</pre>
                <h3>Raw data</h3>
                <pre>{$rawData|var_dump}</pre>
            {/if}
        {/if}
    </div>

    {include file='tpl_inc/licenses_scripts.tpl'}
{else}
    <div class="alert alert-warning fade show" role="alert">
        <i class="fal fa-exclamation-triangle mr-2"></i>
        {__('Safe mode enabled.')} - {__('My purchases')}
    </div>
{/if}
{include file='tpl_inc/footer.tpl'}
