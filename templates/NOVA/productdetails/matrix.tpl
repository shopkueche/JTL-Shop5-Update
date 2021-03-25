{block name='productdetails-matrix'}
    {if $showMatrix}
        <div class="product-matrix clearfix">
            {if $Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_anzeigeformat === 'L' && $Artikel->nIstVater == 1 && $Artikel->oVariationKombiKinderAssoc_arr|count > 0}
                {block name='productdetails-index-include-matrix-list'}
                    {include file='productdetails/matrix_list.tpl'}
                {/block}
            {else}
                {block name='productdetails-index-include-matrix-classic'}
                    {include file='productdetails/matrix_classic.tpl'}
                {/block}
            {/if}
         </div>
    {/if}
{/block}
