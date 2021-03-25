<div id="new-surcharge-notice"></div>
<form id="new-surcharge-form">
    {$jtl_token}
    <input type="hidden" name="neuerZuschlag" value="1" />
    <input type="hidden" name="cISO" value="" />
    <input type="hidden" name="kVersandart" value="0" />
    <input type="hidden" name="kVersandzuschlag" value="{if isset($surchargeID)}{$surchargeID}{else}0{/if}" />

    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('isleList')}:</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
            <input class="form-control" type="text" id="cName" name="cName" value="{if isset($surchargeNew)}{$surchargeNew->getTitle()}{/if}" tabindex="1" required/>
        </div>
    </div>
    {foreach $availableLanguages as $language}
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cName_{$language->getIso()}">{__('showedName')} ({$language->getLocalizedName()}):</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <input class="form-control"
                       type="text"
                       id="cName_{$language->getIso()}"
                       name="cName_{$language->getIso()}" value="{if isset($surchargeNew)}{$surchargeNew->getName({$language->getId()})}{/if}"/>
            </div>
        </div>
    {/foreach}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="fZuschlag">{__('additionalFee')} ({__('amount')}):</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
            <input type="text" id="fZuschlag" name="fZuschlag" value="{if isset($surchargeNew)}{$surchargeNew->getSurcharge()}{/if}" class="form-control price_large" required>
        </div>
    </div>
    <div class="row">
        <div class="ml-auto col-sm-6 col-lg-auto mb-2">
            <button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal">
                {__('cancelWithIcon')}
            </button>
        </div>
        <div class="col-sm-6 col-lg-auto ">
            <button type="submit" class="btn btn-primary btn-block">
                {__('saveWithIcon')}
            </button>
        </div>
    </div>
</form>

<script>
    $('#new-surcharge-form button[type="submit"]').on('click', function(e){
        e.preventDefault();
        ioCall('saveShippingSurcharge', [$('#new-surcharge-form').serializeArray()], function (data) {
            if (data.error) {
                $('#new-surcharge-notice').html(data.message);
            } else{
                if (data.reload) {
                    location.reload();
                } else {
                    $('.surcharge-box[data-surcharge-id="' + data.id + '"] .surcharge-title').html(data.title);
                    $('.surcharge-box[data-surcharge-id="' + data.id + '"] .surcharge-surcharge').html(data.priceLocalized);
                    $('#new-surcharge-modal').modal('hide');
                }
            }
        });
    });
</script>
