{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

<div class="toolbar">
    <form method="get">
        {foreach $cParam_arr as $cParamName => $cParamValue}
            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
        {/foreach}
        <div class="form-row align-items-end">
            {foreach $oFilter->getFields() as $oField}
                {if $oField->getType() === 'text'}
                    {if $oField->isCustomTestOp()}
                        <div class="col-md-6 col-lg-3 col-xl-2">
                            <div class="form-group">
                                <label for="{$oFilter->getId()}_{$oField->getId()}">{$oField->getTitle()}:</label>
                                <select class="custom-select"
                                        name="{$oFilter->getId()}_{$oField->getId()}_op"
                                        id="{$oFilter->getId()}_{$oField->getId()}_op">
                                    {if $oField->getDataType() == 0}
                                        <option value="1"{if $oField->getTestOp() == 1} selected{/if}>{__('contains')}</option>
                                        <option value="2"{if $oField->getTestOp() == 2} selected{/if}>{__('startsWith')}</option>
                                        <option value="3"{if $oField->getTestOp() == 3} selected{/if}>{__('endsWith')}</option>
                                        <option value="4"{if $oField->getTestOp() == 4} selected{/if}>{__('isEqual')}</option>
                                        <option value="9"{if $oField->getTestOp() == 9} selected{/if}>{__('isNotEqual')}</option>
                                    {elseif $oField->getDataType() == 1}
                                        <option value="4"{if $oField->getTestOp() == 4} selected{/if}>=</option>
                                        <option value="9"{if $oField->getTestOp() == 9} selected{/if}>!=</option>
                                        <option value="5"{if $oField->getTestOp() == 5} selected{/if}>&lt;</option>
                                        <option value="6"{if $oField->getTestOp() == 6} selected{/if}>&gt;</option>
                                        <option value="7"{if $oField->getTestOp() == 7} selected{/if}>&lt;=</option>
                                        <option value="8"{if $oField->getTestOp() == 8} selected{/if}>&gt;=</option>
                                    {/if}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 col-xl-2">
                            <div class="form-group">
                    {else}
                        <div class="col-md-6 col-lg-3 col-xl-2">
                            <div class="form-group">
                                <label for="{$oFilter->getId()}_{$oField->getId()}">{$oField->getTitle()}:</label>
                    {/if}
                                <input type="{if $oField->getDataType() == 1}number{else}text{/if}"
                                       class="form-control" name="{$oFilter->getId()}_{$oField->getId()}"
                                       id="{$oFilter->getId()}_{$oField->getId()}"
                                       value="{$oField->getValue()}" placeholder="{$oField->getTitle()}"
                                       {if $oField->getTitleLong() !== ''}data-toggle="tooltip"
                                       data-placement="bottom" title="{$oField->getTitleLong()}"{/if}>
                            </div>
                        </div>
                {elseif $oField->getType() === 'select'}
                    <div class="col-md-6 col-lg-3 col-xl-2">
                        <div class="form-group">
                            <label for="{$oFilter->getId()}_{$oField->getId()}">{$oField->getTitle()}:</label>
                            <select class="custom-select"
                                    name="{$oFilter->getId()}_{$oField->getId()}"
                                    id="{$oFilter->getId()}_{$oField->getId()}"
                                    {if $oField->getTitleLong() !== ''}data-toggle="tooltip"
                                    data-placement="bottom" title="{$oField->getTitleLong()}"{/if}
                                    {if $oField->reloadOnChange}onchange="$('#{$oFilter->getId()}_btn_filter').click()"{/if}>
                                {foreach $oField->getOptions() as $i => $oOption}
                                    <option value="{$i}"{if $i == (int)$oField->getValue()} selected{/if}>
                                        {$oOption->getTitle()}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                {elseif $oField->getType() === 'daterange'}
                    <div class="col-md-6 col-lg-3 col-xl-2">
                        <div class="form-group">
                            <label for="{$oFilter->getId()}_{$oField->getId()}">{__($oField->getTitle())}:</label>
                            <input type="text"  class="form-control"
                                   name="{$oFilter->getId()}_{$oField->getId()}"
                                   id="{$oFilter->getId()}_{$oField->getId()}">
                            {include
                                file="snippets/daterange_picker.tpl"
                                datepickerID="#{$oFilter->getId()}_{$oField->getId()}"
                                currentDate="{$oField->getValue()}"
                                format="DD.MM.YYYY"
                                separator="{__('datepickerSeparator')}"
                            }
                        </div>
                    </div>
                {/if}
            {/foreach}
            <div class="col-md-auto">
                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-primary btn-block" name="action" value="{$oFilter->getId()}_filter"
                            title="{__('useFilter')}" id="{$oFilter->getId()}_btn_filter">
                        <i class="fal fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-auto">
                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-outline-primary btn-block" name="action" value="{$oFilter->getId()}_resetfilter"
                            title="{__('resetFilter')}" id="{$oFilter->getId()}_btn_resetfilter">
                        <i class="fa fa-eraser"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
