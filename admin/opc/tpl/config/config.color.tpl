{if !isset($propid)}
    {$propid = $propname}
{/if}
<div class="form-group no-pb">
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
    <div class="input-group" id="config-{$propid}-group">
        <input type="text" class="form-control colorpicker-input" name="{$propname}" value="{$propval|escape:'html'}"
               {if $required}required{/if} id="config-{$propid}" autocomplete="off"
               placeholder="{__('Default colour')}">
        <span class="input-group-append">
            <span class="input-group-text colorpicker-input-addon"><i></i></span>
        </span>
    </div>
    <script>
        $('#config-{$propid}-group').colorpicker({
            format: '{$propdesc.colorFormat|default:'rgba'}',
            fallbackColor: 'rgba(0,0,0,1.0)',
            extensions: [
                {
                    name: 'swatches',
                    options: {
                        colors: {
                            'green': '#098B1B',
                            'red': '#B90000',
                            'orange': '#F39932',
                            'yellow': '#F8BF00',
                            'darkgrey': '#525252',
                            'grey': '#707070',
                            'mediumgrey': '#9b9b9b',
                            'sand': '#EBEBEB',

                            'lightgreen': '#CDE1D6',
                            'lightred': '#E8CCD2',
                            'lightorange': '#F9F2DC',
                            'lightyellow': '#f8edc7',
                            'lightgrey': '#F5F7FA',
                            'coolwhite': '#F8F8F8',
                        },
                        namesAsValues: true
                    }
                }
            ]
        });
    </script>
</div>