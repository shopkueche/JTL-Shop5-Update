{include file='tpl_inc/seite_header.tpl' cTitel=__('couponStatistic') cDokuURL=__('couponstatisticsURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <form method="post" action="kuponstatistik.php" class="form-inline">
                    {$jtl_token}
                    <div class="row">
                        <div class="col-sm-6 col-xl-auto mb-3">
                            <div class="form-row">
                                <label class="col-sm-auto col-form-label" for="SelectFromDay">{__('fromUntilDate')}:</label>
                                <input type="hidden" name="formFilter" value="1" class="form-control"/>
                                <div class="col-sm-auto">
                                    <input type="text" size="21" name="daterange" class="form-control"/>
                                    {include
                                    file="snippets/daterange_picker.tpl"
                                    datepickerID='input[name="daterange"]'
                                    currentDate="{$startDate} - {$endDate}"
                                    format="YYYY-MM-DD"
                                    separator=" - "
                                    }
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-auto mb-3">
                            <select id="kKupon" name="kKupon" class="combo custom-select">
                                <option value="-1">{__('all')}</option>
                                {foreach $coupons_arr as $coupon_arr}
                                    <option value="{$coupon_arr.kKupon}"{if isset($coupon_arr.aktiv) && $coupon_arr.aktiv} selected{/if}>{$coupon_arr.cName}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-sm-6 col-xl-auto mb-3">
                            <button name="btnSubmit" type="submit" value="Filtern" class="btn btn-primary btn-block">
                                <span class="fal fa-filter"></span> {__('filtering')}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <td class="min-w-sm">{__('countUsedCoupons')}:</td>
                        <td class="min-w-sm"><strong>{$overview_arr.nCountUsedCouponsOrder} ({$overview_arr.nPercentCountUsedCoupons}%)</strong></td>
                    </tr>
                    <tr>
                        <td>{__('countOrders')}:</td>
                        <td><strong>{$overview_arr.nCountOrder}</strong></td>
                    </tr>
                    <tr>
                        <td>{__('countCustomers')}:</td>
                        <td><strong>{$overview_arr.nCountCustomers}</strong></td>
                    </tr>
                    <tr>
                        <td>{__('couponAmountAll')}:</td>
                        <td><strong>{$overview_arr.nCouponAmountAll}</strong></td>
                    </tr>
                    <tr>
                        <td>{__('shoppingCartAmountAll')}:</td>
                        <td><strong>{$overview_arr.nShoppingCartAmountAll}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    {if $usedCouponsOrder|@count > 0}
    <div class="card">
        <div class="table-responsive card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{__('coupon')}</th>
                        <th>{__('customer')}</th>
                        <th class="text-center">{__('orderNumberShort')}</th>
                        <th class="text-center">{__('couponValue')}</th>
                        <th class="text-center">{__('orderValue')}</th>
                        <th>{__('date')}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $usedCouponsOrder as $usedCouponOrder}
                        <tr>
                            <td>
                                {if $usedCouponOrder.kKupon}
                                    <a href="kupons.php?&kKupon={$usedCouponOrder.kKupon}&token={$smarty.session.jtl_token}">{$usedCouponOrder.cName}</a>
                                {else}
                                    {$usedCouponOrder.cName}
                                {/if}
                            </td>
                            <td>{$usedCouponOrder.cUserName}</td>
                            <td class="text-center">{$usedCouponOrder.cBestellNr}</td>
                            <td class="text-center">{$usedCouponOrder.nCouponValue}</td>
                            <td class="text-center">{$usedCouponOrder.nShoppingCartAmount}</td>
                            <td>{$usedCouponOrder.dErstellt|date_format:'%d.%m.%Y %H:%M:%S'}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-link" data-toggle="modal" data-target="#order_{$usedCouponOrder.cBestellNr}">
                                        <span class="icon-hover">
                                            <span class="fal fa-info"></span>
                                            <span class="fas fa-info"></span>
                                        </span>
                                    </button>
                                </div>

                                <div class="modal fade bs-example-modal-lg" id="order_{$usedCouponOrder.cBestellNr}" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <strong>{__('order')}: </strong>
                                                <span class="ml-2">{$usedCouponOrder.cBestellNr} ({$usedCouponOrder.cUserName})</span>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <i class="fal fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                        <tr>
                                                            <th>{__('orderPosition')}</th>
                                                            <th>{__('count')}</th>
                                                            <th>{__('unitPrice')}</th>
                                                            <th>{__('totalPrice')}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        {foreach $usedCouponOrder.cOrderPos_arr as $cOrderPos_arr}
                                                            <tr>
                                                                <td>{$cOrderPos_arr.cName}</td>
                                                                <td>{$cOrderPos_arr.nAnzahl}</td>
                                                                <td>{$cOrderPos_arr.nPreis}</td>
                                                                <td>{$cOrderPos_arr.nGesamtPreis}</td>
                                                            </tr>
                                                        {/foreach}
                                                        </tbody>
                                                        <tfoot>
                                                        <tr>
                                                            <td>{__('totalAmount')}:</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>{$usedCouponOrder.nShoppingCartAmount}</td>
                                                        </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>