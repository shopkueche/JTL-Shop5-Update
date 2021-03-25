{if !isset($propid)}
    {$propid = $propname}
{/if}
<div class="form-group">
    <label for="config-{$propid}"
            {if !empty($propdesc.desc)}
                data-toggle="tooltip" title="{$propdesc.desc|default:''}"
                data-placement="auto"
            {/if}>
        {$propdesc.label}
        {if !empty($propdesc.desc)}
            <i class="fas fa-info-circle fa-fw"></i>
        {/if}
    </label>
    <div class="select-wrapper">
        <select class="form-control" id="config-{$propid}" name="{$propname}" {if $required === true}required{/if}>
            {foreach $propdesc.options as $value => $label}
                {if is_string($label)}
                    <option value="{$value}" {if $value == $propval}selected{/if}>
                        {$label}
                    </option>
                {else}
                    {$subgroup = $label}

                    <optgroup label="{$subgroup.label}">
                        {foreach $subgroup.options as $value => $label}
                            <option value="{$value}" {if $value == $propval}selected{/if}>
                                {$label}
                            </option>
                        {/foreach}
                    </optgroup>
                {/if}
            {/foreach}
        </select>
    </div>
</div>

{if isset($propdesc.childrenFor)}
    <script>
        (function() {
            let selectElm = $('#config-{$propid}');
            let option = selectElm.find(':selected').val();

            selectElm.on('change', () => {
                let option = selectElm.find(':selected').val();

                $('.childrenFor-{$propid}').collapse('hide');
                $('#childrenFor-' + option + '-{$propid}').collapse('show');
            });

            $(() => {
                $('#childrenFor-' + option + '-{$propid}').collapse('show');
            });
        })();
    </script>
{/if}