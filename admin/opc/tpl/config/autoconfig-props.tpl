{foreach $props as $propname => $propdesc}
    {$type = $propdesc.type|default:'text'}
    {$required = $propdesc.required|default:false}
    {$width = round(($propdesc.width|default:100) * 12 / 100)}
    {$rowWidthAccu = $rowWidthAccu + $width}

    {if !empty($propdesc.order)}
        {$order = 'order-'|cat:$propdesc.order}
    {else}
        {$order = ''}
    {/if}

    {if $instance->hasProperty($propname)}
        {$propval = $instance->getProperty($propname)}
    {else}
        {$propval = $propDesc.default|default:null}
    {/if}

    <div class="col-{$width} {$order}">
        {if $type === 'text' || $type === 'email' || $type === 'password' || $type === 'number'
                || $type === 'date' || $type === 'time'}
            <div class="form-group">
                <label for="config-{$propname}"
                        {if !empty($propdesc.desc)}
                            data-toggle="tooltip" title="{$propdesc.desc|default:''|escape:'html'}"
                            data-placement="auto"
                        {/if}>
                    {$propdesc.label}
                    {if !empty($propdesc.desc)}
                        <i class="fas fa-info-circle fa-fw"></i>
                    {/if}
                </label>
                <input type="{$type}" class="form-control" id="config-{$propname}" name="{$propname}"
                       value="{$propval|escape:'html'}"
                       {if !empty($propdesc.placeholder)}placeholder="{$propdesc.placeholder|escape:'html'}"{/if}
                       {if $required === true}required{/if}>
                {if isset($propdesc.help)}
                    <span class="help-block">{$propdesc.help}</span>
                {/if}
            </div>
        {else}
            {include file="./config."|cat:$type|cat:".tpl"}
        {/if}
    </div>

    {if $rowWidthAccu >= 12}
        {$rowWidthAccu = 0}

        </div><div class="row">
    {/if}

    {if isset($propdesc.children)}
        <div id="children-{$propname}" class="col-12 collapse">
            <div class="row">
                {include file='./autoconfig-props.tpl' props=$propdesc.children}
            </div>
        </div>
    {/if}

    {if isset($propdesc.childrenFor)}
        {foreach $propdesc.childrenFor as $option => $childProps}
            <div id="childrenFor-{$option}-{$propname}"
                 class="col-12 collapse childrenFor-{$propname}">
                <div class="row">
                    {include file='./autoconfig-props.tpl' props=$childProps}
                </div>
            </div>
        {/foreach}
    {/if}
{/foreach}