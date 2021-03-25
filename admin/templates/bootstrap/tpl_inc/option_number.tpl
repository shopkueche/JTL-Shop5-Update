<div class="input-group form-counter">
    <div class="input-group-prepend">
        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
            <span class="fas fa-minus"></span>
        </button>
    </div>
    <input class="form-control" type="number" name="{$setting->elementID}"
       id="{$setting->elementID}"
       value="{$setting->value|escape:'html'}"
       placeholder="{__($setting->cPlaceholder)}" />
    <div class="input-group-append">
        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
            <span class="fas fa-plus"></span>
        </button>
    </div>
</div>
