{$style = $instance->getProperty('listStyle')}

{if $isPreview}
    <div class="opc-ProductStream" style="{$instance->getStyleString()}">
        {image alt='ProductStream' src=$portlet->getBaseUrl()|cat:'preview.'|cat:$style|cat:'.png'}
    </div>
{else}
    {$productlist = $portlet->getFilteredProducts($instance)}
    
    {if $style === 'list' || $style === 'gallery'}
        {if $style === 'list'}
            {$grid = '12'}
            {$eqHeightClasses = ''}
        {else}
            {$grid   = '6'}
            {$gridmd = '4'}
            {$eqHeightClasses = 'row-eq-height row-eq-img-height'}
        {/if}
        {if $inContainer === false}
            <div class="container-fluid">
        {/if}
        {row class=$style|cat:' '|cat:$eqHeightClasses|cat:' product-list opc-ProductStream opc-ProductStream-'|cat:$style|cat:' '|cat:$instance->getStyleClasses()
            itemprop="mainEntity"
            itemscope=true
            itemtype="http://schema.org/ItemList"
            style="{$instance->getStyleString()}"}
            {foreach $productlist as $Artikel}
                {col cols={$grid} md="{if isset($gridmd)}{$gridmd}{/if}"
                     class="product-wrapper {if !($style === 'list' && $Artikel@last)}mb-4{/if}"
                     itemprop="itemListElement" itemscope=true itemtype="http://schema.org/Product"}
                    {if $style === 'list'}
                        {include file='productlist/item_list.tpl' tplscope=$style}
                    {elseif $style === 'gallery'}
                        {include file='productlist/item_box.tpl' tplscope=$style class='thumbnail'}
                    {/if}
                {/col}
            {/foreach}
        {/row}
        {if $inContainer === false}
            </div>
        {/if}
    {elseif $style === 'simpleSlider'}
        <div id="{$instance->getUid()}"
             class="carousel carousel-arrows-inside evo-slider slick-lazy
                    opc-ProductStream opc-ProductStream-slider slick-type-product"
             data-slick-type="product-slider"
             style="{$instance->getStyleString()}">
            {foreach $productlist as $Artikel}
                <div class="product-wrapper">
                    <a href="{$Artikel->cURLFull}">
                        <div class="square square-image">
                            <div class="inner">
                                <img src="{$Artikel->Bilder[0]->cURLNormal}" alt="{$Artikel->cName}"
                                     title="{$Artikel->cName}">
                            </div>
                        </div>
                    </a>
                </div>
            {/foreach}
        </div>
    {elseif $style === 'slider'}
        {if $inContainer === false}
            <div class="container-fluid">
        {/if}
        <div class="opc-product-slider" style="{$instance->getStyleString()}">
            {include file='snippets/product_slider.tpl' productlist=$productlist isOPC=true}
        </div>
        {if $inContainer === false}
            </div>
        {/if}
    {elseif $style === 'box-slider'}
        <div class="opc-product-slider" style="{$instance->getStyleString()}">
            {include file='snippets/product_slider.tpl'
                productlist=$productlist
                tplscope='box'
                isOPC=true
            }
        </div>
    {/if}
{/if}