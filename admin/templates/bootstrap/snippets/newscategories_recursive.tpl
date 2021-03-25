{foreach $oNewsKategorie_arr as $oNewsKategorie}
    <option value="{$oNewsKategorie->getID()}"
            {if isset($selectedCat)}
                {if is_array($selectedCat)}
                    {foreach $selectedCat as $singleCat}
                        {if $singleCat == $oNewsKategorie->getID()} selected{/if}
                    {/foreach}
                {elseif $selectedCat == $oNewsKategorie->getID()} selected{/if}
            {/if}>
            {for $j=1 to $i}&nbsp;&nbsp;&nbsp;{/for}{$oNewsKategorie->getName()}
    </option>
    {if count($oNewsKategorie->getChildren()) > 0}
        {include file='snippets/newscategories_recursive.tpl' i=$i+1 oNewsKategorie_arr=$oNewsKategorie->getChildren() selectedCat=$selectedCat}
    {/if}
{/foreach}