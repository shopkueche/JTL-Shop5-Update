{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('Trennzeichen') cBeschreibung=__('trennzeichenDesc') cDokuURL=__('trennzeichenURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl' action='trennzeichen.php'}
        </div>
    </div>
    <form method="post" action="trennzeichen.php">
        {$jtl_token}
        <input type="hidden" name="save" value="1" />
        <div id="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('divider')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body table-responsive">
                    <table class="list table">
                    <thead>
                    <tr>
                        <th class="text-left">{__('unit')}</th>
                        <th class="text-center">{__('countDecimals')}</th>
                        <th class="text-center">{__('decimalsDivider')}</th>
                        <th class="text-center">{__('thousandDivider')}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        {assign var=nDezimal_weight value="nDezimal_"|cat:$smarty.const.JTL_SEPARATOR_WEIGHT}
                        {assign var=cDezZeichen_weight value="cDezZeichen_"|cat:$smarty.const.JTL_SEPARATOR_WEIGHT}
                        {assign var=cTausenderZeichen_weight value="cTausenderZeichen_"|cat:$smarty.const.JTL_SEPARATOR_WEIGHT}
                        {if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT])}
                            <input type="hidden" name="kTrennzeichen_{$smarty.const.JTL_SEPARATOR_WEIGHT}" value="{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT]->getTrennzeichen()}" />
                        {/if}
                        <td class="text-left">{__('weight')}</td>
                        <td class="widthheight text-center">
                            <div class="input-group form-counter{if isset($xPlausiVar_arr[$nDezimal_weight])} form-error{/if}">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                        <span class="fas fa-minus"></span>
                                    </button>
                                </div>
                                <input size="2" type="number" name="nDezimal_{$smarty.const.JTL_SEPARATOR_WEIGHT}" class="form-control" value="{if isset($xPostVar_arr[$nDezimal_weight])}{$xPostVar_arr[$nDezimal_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT]->getDezimalstellen()}{/if}{/if}" />
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                        <span class="fas fa-plus"></span>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="widthheight text-center">
                            <input size="2" type="text" name="cDezZeichen_{$smarty.const.JTL_SEPARATOR_WEIGHT}" class="m-auto form-control{if isset($xPlausiVar_arr[$cDezZeichen_weight])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cDezZeichen_weight])}{$xPostVar_arr[$cDezZeichen_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT]->getDezimalZeichen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight text-center">
                            <input size="2" type="text" name="cTausenderZeichen_{$smarty.const.JTL_SEPARATOR_WEIGHT}" class="m-auto form-control{if isset($xPlausiVar_arr[$cTausenderZeichen_weight])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cTausenderZeichen_weight])}{$xPostVar_arr[$cTausenderZeichen_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT]->getTausenderZeichen()}{/if}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        {assign var=nDezimal_amount value="nDezimal_"|cat:$smarty.const.JTL_SEPARATOR_AMOUNT}
                        {assign var=cDezZeichen_amount value="cDezZeichen_"|cat:$smarty.const.JTL_SEPARATOR_AMOUNT}
                        {assign var=cTausenderZeichen_amount value="cTausenderZeichen_"|cat:$smarty.const.JTL_SEPARATOR_AMOUNT}
                        {if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT])}
                            <input type="hidden" name="kTrennzeichen_{$smarty.const.JTL_SEPARATOR_AMOUNT}" value="{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT]->getTrennzeichen()}" />
                        {/if}
                        <td class="text-left">{__('quantity')}</td>
                        <td class="widthheight text-center">
                            <div class="input-group form-counter{if isset($xPlausiVar_arr[$nDezimal_amount])} form-error{/if}">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                        <span class="fas fa-minus"></span>
                                    </button>
                                </div>
                                <input size="2" type="number" name="nDezimal_{$smarty.const.JTL_SEPARATOR_AMOUNT}" class="form-control" value="{if isset($xPostVar_arr[$nDezimal_amount])}{$xPostVar_arr[$nDezimal_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT]->getDezimalstellen()}{/if}{/if}" />
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                        <span class="fas fa-plus"></span>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="widthheight text-center">
                            <input size="2" type="text" name="cDezZeichen_{$smarty.const.JTL_SEPARATOR_AMOUNT}" class="m-auto form-control{if isset($xPlausiVar_arr[$cDezZeichen_amount])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cDezZeichen_amount])}{$xPostVar_arr[$cDezZeichen_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT]->getDezimalZeichen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight text-center">
                            <input size="2" type="text" name="cTausenderZeichen_{$smarty.const.JTL_SEPARATOR_AMOUNT}" class="m-auto form-control{if isset($xPlausiVar_arr[$cTausenderZeichen_amount])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cTausenderZeichen_amount])}{$xPostVar_arr[$cTausenderZeichen_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT]->getTausenderZeichen()}{/if}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        {assign var=nDezimal_length value="nDezimal_"|cat:$smarty.const.JTL_SEPARATOR_LENGTH}
                        {assign var=cDezZeichen_length value="cDezZeichen_"|cat:$smarty.const.JTL_SEPARATOR_LENGTH}
                        {assign var=cTausenderZeichen_length value="cTausenderZeichen_"|cat:$smarty.const.JTL_SEPARATOR_LENGTH}
                        {if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH])}
                            <input type="hidden" name="kTrennzeichen_{$smarty.const.JTL_SEPARATOR_LENGTH}" value="{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH]->getTrennzeichen()}" />
                        {/if}
                        <td class="text-left">{__('length')}</td>
                        <td class="widthheight text-center">
                            <div class="input-group form-counter{if isset($xPlausiVar_arr[$nDezimal_length])} form-error{/if}">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                        <span class="fas fa-minus"></span>
                                    </button>
                                </div>
                                <input size="2" type="number" name="nDezimal_{$smarty.const.JTL_SEPARATOR_LENGTH}" class="form-control" value="{if isset($xPostVar_arr[$nDezimal_length])}{$xPostVar_arr[$nDezimal_length]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH]->getDezimalstellen()}{/if}{/if}" />
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                        <span class="fas fa-plus"></span>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="widthheight text-center">
                            <input size="2" type="text" name="cDezZeichen_{$smarty.const.JTL_SEPARATOR_LENGTH}" class="m-auto form-control{if isset($xPlausiVar_arr[$cDezZeichen_length])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cDezZeichen_length])}{$xPostVar_arr[$cDezZeichen_length]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH]->getDezimalZeichen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight text-center">
                            <input size="2" type="text" name="cTausenderZeichen_{$smarty.const.JTL_SEPARATOR_LENGTH}" class="m-auto form-control{if isset($xPlausiVar_arr[$cTausenderZeichen_length])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cTausenderZeichen_length])}{$xPostVar_arr[$cTausenderZeichen_length]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH]->getTausenderZeichen()}{/if}{/if}" />
                        </td>
                    </tr>

                    </tbody>
                </table>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
