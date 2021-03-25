{$x = $parentSmarty->assign('tabIDs', [])}
{$x = $parentSmarty->assign('tabContents', [])}

<{$params.tag}
    class="nav nav-tabs {$params.class->getValue()}{if $params.pills->getValue() === true} nav-pills{/if}"
    {*{if $params.swipeable->getValue() === false}*}role="tablist"{*{/if}*}
    {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
    {if $params.style->hasValue()}style="{$params.style}"{/if}
>
    {$blockContent}
</{$params.tag}>
<div class="tab-content"{if $params.id->hasValue()} id="tab-content-{$params.id->getValue()}"{/if}
    {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
    {if $params.itemscope->getValue() === true}itemscope {/if}
    {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
    {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
>
    {foreach $tabContents as $i => $tab}
        {*{if $params.swipeable->getValue() === true}
            <div class="pt-3"
                 id="tab-{$tabIDs[$i]}"
                 aria-labelledby="tab-link-{$tabIDs[$i]}">
                {$tab}
            </div>
        {else}*}
            <div class="tab-pane fade pt-3{if $tab@index === 0} in active{/if}"
                 id="tab-{$tabIDs[$i]}"
                 role="tabpanel"
                 aria-labelledby="tab-link-{$tabIDs[$i]}">
                {$tab}
            </div>
        {*{/if}*}
    {/foreach}
</div>
{*
{if $params.swipeable->getValue() === true}
    <script>
        $(window).on('load', function () {
            /*tabs 2 slider*/
            $('#product-tabs').slick({
                slidesToShow:   4,
                slidesToScroll: 1,
                arrows:         false,
                infinite:       false,
                focusOnSelect:  true,
                variableWidth:  true,
                asNavFor:       '#tab-content-product-tabs',
                responsive:     [
                    {
                        breakpoint: 768,
                        settings:   {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 576,
                        settings:   {
                            slidesToShow: 2
                        }
                    }
                ],
            });
            $('#tab-content-product-tabs').slick({
                slidesToShow:   1,
                slidesToScroll: 1,
                arrows:         false,
                infinite:       false,
                adaptiveHeight: true,
                asNavFor:       '#product-tabs'
            });
        });
    </script>
{/if}*}