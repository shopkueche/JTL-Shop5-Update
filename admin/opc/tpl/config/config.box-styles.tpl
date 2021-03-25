<div class="box-styles row">
    <div class="box-config col-8">
        <div class="outer-box">
            <div class="top-row">
                <div>
                    {__('margin')} (px)
                </div>
                <label class="mid-top-col">
                    <input id="margin-top-input" class="form-control" tabindex="1"
                           name="{$propname}[margin-top]" value="{$propval['margin-top']|default:''|escape:'html'}">
                </label>
                <div class="one-third"></div>
            </div>
            <div class="mid-row">
                <label>
                    <input id="margin-left-input" class="form-control" tabindex="4"
                           name="{$propname}[margin-left]" value="{$propval['margin-left']|default:''|escape:'html'}">
                </label>
                <div class="border-box">
                    <div class="top-row">
                        <div>
                            {__('border')} (px)
                        </div>
                        <label class="mid-top-col">
                            <input id="border-top-input" class="form-control" tabindex="5"
                                   name="{$propname}[border-top-width]"
                                   value="{$propval['border-top-width']|default:''|escape:'html'}">
                        </label>
                        <div class="one-third"></div>
                    </div>
                    <div class="mid-row">
                        <label>
                            <input id="border-left-input" class="form-control" tabindex="8"
                                   name="{$propname}[border-left-width]"
                                   value="{$propval['border-left-width']|default:''|escape:'html'}">
                        </label>
                        <div class="padding-box">
                            <div class="top-row">
                                <div>
                                    {__('padding')} (px)
                                </div>
                                <label class="mid-top-col">
                                    <input id="padding-top-input" class="form-control" tabindex="9"
                                           name="{$propname}[padding-top]"
                                           value="{$propval['padding-top']|default:''|escape:'html'}">
                                </label>
                                <div class="one-third"></div>
                            </div>
                            <div class="mid-row">
                                <label>
                                    <input id="padding-left-input" class="form-control" tabindex="12"
                                           name="{$propname}[padding-left]"
                                           value="{$propval['padding-left']|default:''|escape:'html'}">
                                </label>
                                <div class="content-box"></div>
                                <label>
                                    <input id="padding-right-input" class="form-control" tabindex="10"
                                           name="{$propname}[padding-right]"
                                           value="{$propval['padding-right']|default:''|escape:'html'}">
                                </label>
                            </div>
                            <label class="bottom-row">
                                <input id="padding-bottom-input" class="form-control" tabindex="11"
                                       name="{$propname}[padding-bottom]"
                                       value="{$propval['padding-bottom']|default:''|escape:'html'}">
                            </label>
                        </div>
                        <label>
                            <input id="border-right-input" class="form-control" tabindex="6"
                                   name="{$propname}[border-right-width]"
                                   value="{$propval['border-right-width']|default:''|escape:'html'}">
                        </label>
                    </div>
                    <label class="bottom-row">
                        <input id="border-bottom-input" class="form-control" tabindex="7"
                               name="{$propname}[border-bottom-width]"
                               value="{$propval['border-bottom-width']|default:''|escape:'html'}">
                    </label>
                </div>
                <label>
                    <input id="margin-right-input" class="form-control" tabindex="2"
                           name="{$propname}[margin-right]"
                           value="{$propval['margin-right']|default:''|escape:'html'}">
                </label>
            </div>
            <label class="bottom-row">
                <input id="margin-bottom-input" class="form-control" tabindex="3"
                       name="{$propname}[margin-bottom]"
                       value="{$propval['margin-bottom']|default:''|escape:'html'}">
            </label>
        </div>
    </div>
    <div class="border-config col-4">
        {include file="./config.select.tpl"
            propname="{$propname}[border-style]"
            propid="{$propname}-border-style"
            propval=$propval['border-style']|default:''
            propdesc=[
                'label'   => __('Border style'),
                'options' => [
                    ''       => __('unset'),
                    'dotted' => __('dotted'),
                    'dashed' => __('dashed'),
                    'solid'  => __('solid')
                ]
            ]}
        {include file="./config.color.tpl"
            propname="{$propname}[border-color]"
            propid="{$propname}-border-color"
            propval=$propval['border-color']|default:''
            propdesc=[
                'label'   => __('Border colour')
            ]
        }
        <div class='form-group'>
            <label for="config-{$propname}-border-radius"
                   data-toggle="tooltip" title="{__('cssNumericDesc')}" data-placement="auto">
                {__('Border radius')}
                <i class="fas fa-info-circle fa-fw"></i>
            </label>
            <input type="text" class="form-control" id="config-{$propname}-border-radius"
                   name="{$propname}[border-radius]"
                   value="{$propval['border-radius']|default:''|escape:'html'}">
        </div>
    </div>
</div>