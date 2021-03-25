{block name='checkout-coupon-form'}
    {if $KuponMoeglich == 1}
        {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form jtl-validate coupon-form" slide=true}
            {block name='checkout-coupon-form-form-content'}
                {input type="hidden" name="pruefekupon" value="1"}
                <fieldset>
                    {inputgroup}
                        {input type="text"
                            name="Kuponcode"
                            maxlength="32"
                            value="{if !empty($Kuponcode)}{$Kuponcode}{/if}"
                            id="kupon"
                            placeholder="{lang key='couponCode' section='account data'}"
                            aria=["label"=>"{lang key='couponCode' section='account data'}"]
                            required=true}
                        {inputgroupaddon append=true}
                            {button type="submit" value="1" variant="outline-primary"}{lang key='useCoupon' section='checkout'}{/button}
                        {/inputgroupaddon}
                    {/inputgroup}
                </fieldset>
            {/block}
        {/form}
    {/if}
{/block}
