{assign var=isleListFor value=__('isleListFor')}
{assign var=cVersandartName value=$Versandart->cName}
{assign var=cLandName value=$Land->getName()}
{assign var=cLandISO value=$Land->getISO()}

{include file='tpl_inc/seite_header.tpl'
         cTitel=$isleListFor|cat: ' '|cat:$cVersandartName|cat:', '|cat:$cLandName|cat:'('|cat:$cLandISO|cat:')'
         cBeschreibung=__('isleListsDesc')}
<div class="card">
    <div class="card-body">
        <button id="surcharge-create"
                type="submit"
                class="btn btn-primary"
                data-iso="{$cLandISO}"
                data-versandart-id="{$Versandart->kVersandart}"
                data-toggle="modal"
                data-target="#new-surcharge-modal">
            <i class="fa fa-save"></i> {__('create')}
        </button>
    </div>
</div>
<div class="card">
    <div class="card-body">
        {include file='tpl_inc/pagination.tpl'
                 pagination=$pagination
                 cParam_arr=[
                    'zuschlag'    => 1,
                    'kVersandart' => {$Versandart->kVersandart},
                    'cISO'        => {$cLandISO},
                    'token'       => {$smarty.session.jtl_token}
                 ]}
        <div class="table-responsive list-unstyled-inden">
            <table class="table">
                <thead>
                    <tr>
                        <th>{__('name')}</th>
                        <th class="text-center">{__('additionalFee')}</th>
                        <th>{__('zip')}, {__('zipRange')}</th>
                        <th class="text-center">{__('actions')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $surcharges as $surcharge}
                        <tr class="surcharge-box" data-surcharge-id="{$surcharge->getID()}">
                            <td class="surcharge-title">{$surcharge->getTitle()}</td>
                            <td class="surcharge-surcharge text-center">{$surcharge->getPriceLocalized()}</td>
                            <td class="zip-badge-row">{include file="snippets/zuschlagliste_plz_badges.tpl"}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm surcharge-remove"
                                            data-surcharge-id="{$surcharge->getID()}"
                                            data-toggle="tooltip"
                                            title="{__('additionalFeeDelete')}">
                                        <span class="icon-hover">
                                            <span class="fal fa-trash-alt"></span>
                                            <span class="fas fa-trash-alt"></span>
                                        </span>
                                    </button>
                                    <button class="btn btn-link px-2"
                                            data-toggle="modal"
                                            data-target="#add-zip-modal"
                                            data-surcharge-name="{$surcharge->getName()}"
                                            data-surcharge-id="{$surcharge->getID()}">
                                        <span class="icon-hover" title="{__('addZip')}" data-toggle="tooltip">
                                            <span class="fal fa-plus"></span>
                                            <span class="fas fa-plus"></span>
                                        </span>
                                    </button>
                                    <button class="btn btn-link px-2"
                                            data-toggle="modal"
                                            data-target="#new-surcharge-modal"
                                            data-surcharge-name="{$surcharge->getName()}"
                                            data-surcharge-id="{$surcharge->getID()}">
                                        <span class="icon-hover" title="{__('modify')}" data-toggle="tooltip">
                                            <span class="fal fa-edit"></span>
                                            <span class="fas fa-edit"></span>
                                        </span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer save-wrapper">
        <div class="row">
            <div class="ml-auto col-sm-6 col-lg-auto">
                <a class="btn btn-outline-primary btn-block" href="versandarten.php">
                    {__('goBack')}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add-zip-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="subheading1">{__('addZip')} - <span id="add-zip-modal-title"></span></div>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <hr class="mb-3">
                <div id="add-zip-notice"></div>
                <form id="add-zip-form">
                    <div class="form-group">
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="zip-type-simple" name="zip-type" value="simple" checked>
                            <label class="custom-control-label" for="zip-type-simple">{__('zip')}</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" class="custom-control-input" id="zip-type-area" name="zip-type" value="area">
                            <label class="custom-control-label" for="zip-type-area">{__('orZipRange')}</label>
                        </div>
                    </div>
                    <input type="hidden" id="add-zip-modal-id" name="kVersandzuschlag" value="">
                    <div id="zip-container" class="form-row">
                        <label class="" for="cPLZ">{__('zip')}:</label>
                        <input type="text" id="cPLZ" name="cPLZ" class="form-control zipcode" />
                    </div>
                    <div id="zip-area-container" class="form-row d-none">
                        <label class="" for="cPLZ">{__('zipRange')}:</label>
                        <div class="input-group">
                            <input type="number" name="cPLZAb" class="form-control zipcode" />
                            <span class="input-group-addon">&ndash;</span>
                            <input type="number" name="cPLZBis" class="form-control zipcode" />
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="ml-auto col-sm-6 col-lg-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal">
                                {__('cancelWithIcon')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-lg-auto">
                            <button type="submit" class="btn btn-outline-primary btn-block">
                                {__('addZip')}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="new-surcharge-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div id="new-surcharge-modal-title" class="subheading1">{__('createList')}</div>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <hr class="mb-3">
                <div id="new-surcharge-form-wrapper">
                    {include file='snippets/zuschlagliste_form.tpl'}
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    $('input[name="zip-type"]').on('change', function () {
        if ($(this).val() === 'simple') {
            $('#zip-container').removeClass('d-none');
            $('#zip-area-container').addClass('d-none');
            $('#zip-area-container input').val('');
        } else {
            $('#zip-container').addClass('d-none');
            $('#zip-area-container').removeClass('d-none');
            $('#zip-container input').val('');
        }
    });

    $('button[data-target="#add-zip-modal"]').on('click', function () {
        $('#add-zip-modal-title').html($(this).data('surcharge-name'));
        $('#add-zip-modal-id').val($(this).data('surcharge-id'));
        $('#zip-area-container input').val('');
        $('#zip-container input').val('');
        $('#add-zip-notice').html('');
    });

    $('.surcharge-box button[data-target="#new-surcharge-modal"]').on('click', function () {
        $('#new-surcharge-modal-title').html('{__("editList")} - ' + $(this).data('surcharge-name'));
        $('#new-surcharge-form-wrapper').html('');
        ioCall('getShippingSurcharge', [$(this).data('surcharge-id')], function (data) {
            $('#new-surcharge-form-wrapper').html(data.body);
        });
    });

    $('#surcharge-create').on('click', function () {
        $('#new-surcharge-form-wrapper input').val('');
        $('#new-surcharge-notice').html('');
        $('#new-surcharge-form-wrapper input[name="kVersandart"]').val($(this).data('versandart-id'));
        $('#new-surcharge-form-wrapper input[name="cISO"]').val($(this).data('iso'));
        $('#new-surcharge-modal-title').html('{__("createList")}');
    });

    $('#add-zip-modal button[type="submit"]').on('click', function(e){
        e.preventDefault();
        ioCall('createShippingSurchargeZIP', [$('#add-zip-form').serializeArray()], function (data) {
            $('#add-zip-notice').html(data.message);
            $('.surcharge-box[data-surcharge-id="' + data.surchargeID + '"] .zip-badge-row').html(data.badges);
            setBadgeClick(data.surchargeID);
        });
    });

    $('.surcharge-remove').on('click', function (e) {
        e.preventDefault();
        ioCall('deleteShippingSurcharge', [$(this).data('surcharge-id')], function (data) {
            if (data.surchargeID > 0) {
                $('.surcharge-box[data-surcharge-id="' + data.surchargeID + '"]').remove();
                closeTooltips();
            }
        });
    });

    function setBadgeClick(surchargeID) {
        let surchargeIDText = '';
        if  (surchargeID !== 0) {
            surchargeIDText = '[data-surcharge-id="' + surchargeID + '"]';
        }
        $('.zip-badge' + surchargeIDText).on('click', function(e){
            e.preventDefault();
            ioCall('deleteShippingSurchargeZIP', [$(this).data('surcharge-id'), $(this).data('zip')], function (data) {
                $('.zip-badge[data-surcharge-id="' + data.surchargeID + '"][data-zip="' + data.ZIP + '"]').remove();
                closeTooltips();
            });
        });
    }
    $(window).on('load', function () {
        setBadgeClick(0);
    });
</script>
