{include file='tpl_inc/header.tpl'}
{assign var=cBeschreibung value=__('shoptemplatesDesc')}
{if isset($templateConfig) && $templateConfig}
    {assign var=cTitel value={__('settings')}|cat:': '|cat:$template->getName()}
    {if !empty($template->getDocumentationURL())}
        {assign var=cDokuURL value=$template->getDocumentationURL()}
    {else}
        {assign var=cDokuURL value=__('shoptemplateURL')}
    {/if}
{else}
    {assign var=cTitel value=__('shoptemplates')}
    {assign var=cDokuURL value=__('shoptemplateURL')}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=$cBeschreibung cDokuURL=$cDokuURL}

{*workaround: no async uploads (the fileinput option uploadAsync does not work correctly... *}
<style>#form_settings .fileinput-upload-button, .kv-file-upload{ldelim}display:none!important;{rdelim}</style>

<div id="content">
{if isset($templateConfig) && $templateConfig|count > 0}
    {include file='tpl_inc/shoptemplate_detail.tpl'}
{else}
    {include file='tpl_inc/shoptemplate_overview.tpl'}
    {include file='tpl_inc/shoptemplate_upload.tpl'}
{/if}
</div>
{include file='tpl_inc/footer.tpl'}
