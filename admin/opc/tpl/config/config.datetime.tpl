<div class='form-group'>
    <label for="config-{$propname}">{$propdesc.label}</label>
    <input type="text" class="form-control opc-control datetimepicker-input" id="config-{$propname}" name="{$propname}"
           data-toggle="datetimepicker" data-target="#config-{$propname}" {if $required === true}required{/if}
           {if !empty($propdesc.placeholder)}placeholder="{$propdesc.placeholder}"{/if} autocomplete="off"
           data-prop-type="datetime">
    {if isset($propdesc.help)}
        <span class="help-block">{$propdesc.help}</span>
    {/if}
</div>
<script>

    var dateTimeInput = $('#config-{$propname}');
    opc.gui.initDateTimePicker(dateTimeInput);

    {if !empty($propval)}
        dateTimeInput.val(opc.page.decodeDate('{$propval|escape:'html'}'));
    {/if}
</script>