{if $missingShippingClassCombis === -1}
    <p>
        {__('coverageShippingClassCombination')}
        {{__('noShipClassCombiValidation')}|sprintf:{$smarty.const.SHIPPING_CLASS_MAX_VALIDATION_COUNT}}
    </p>
{/if}
{if $missingShippingClassCombis !== -1}
    <p>{__('coverageShippingClassCombination')}</p>
    <button class="btn btn-warning mb-3" type="button" data-toggle="collapse" data-target="#collapseShippingClasses" aria-expanded="false" aria-controls="collapseShippingClasses">
        {__('showMissingCombinations')}
    </button>
    <div class="collapse" id="collapseShippingClasses">
        <div class="row">
            {foreach $missingShippingClassCombis as $mscc}
                <div class="col-auto"><span class="badge badge-info">{$mscc}</span></div>
            {/foreach}
        </div>
    </div>
{/if}
