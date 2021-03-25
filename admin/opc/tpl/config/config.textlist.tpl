<div class="form-group">
    <label>{$propdesc.label}</label>
    {foreach $propval as $i => $text}
        <label class="sr-only" for="{$propname}-{$i}"></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <button type="button" class="btn"
                        onclick="removeLine_{$propname}(this);">
                    <i class="fas fa-times fa-fw"></i>
                </button>
            </div>
            <input type="text" class="form-control" name="{$propname}[]"
                   value="{$text|escape:'html'}" id="{$propname}-{$i}">
        </div>
    {/foreach}
    <label class="sr-only" for="{$propname}-new"></label>
    <div class="input-group" id="new-input-group-{$propname}">
        <div class="input-group-prepend">
            <button type="button" class="btn primary"
                    onclick="addNewLine_{$propname}()">
                <i class="fas fa-plus fa-fw"></i>
            </button>
        </div>
        <input type="text" class="form-control" id="{$propname}-new" disabled>
    </div>
</div>
<script>
    function removeLine_{$propname}(elm)
    {
        $(elm).closest('.input-group').remove();
    }

    function addNewLine_{$propname}()
    {
        var newInputGroup      = $('#new-input-group-{$propname}');
        var newInputGroupClone = newInputGroup.clone();

        newInputGroupClone.attr('id', '');
        newInputGroupClone.find('button')
            .removeClass('primary')
            .attr('onclick', 'removeLine_{$propname}(this);');
        newInputGroupClone.find('i.fas')
            .removeClass('fa-plus')
            .addClass('fa-times');
        newInputGroupClone.find('input')
            .prop('disabled', false)
            .attr('name', '{$propname}[]');
        newInputGroupClone.insertBefore(newInputGroup);
    }
</script>