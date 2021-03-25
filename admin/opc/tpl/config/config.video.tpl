{$previewVidUrl = $propval|default:$portlet->getDefaultPreviewImageUrl()}

<div class='form-group'>
    <label for="config-{$propname}">{$propdesc.label}</label>
    <input type="hidden" name="{$propname}" value="{$propval|escape:'html'}">
    <button type="button" class="btn btn-default video-btn" onclick="opc.selectVideoProp('{$propname}')">
        <video width="300" height="160" controlsList="nodownload" id="cont-preview-vid-{$propname}">
            <source src="{$previewVidUrl}" id="preview-vid-{$propname}" type="video/mp4">
            {__('videoTagNotSupported')}
        </video>
    </button>
</div>