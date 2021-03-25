{block name='boxes-box-custom'}
    {card class="box box-custom box-normal" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-custom-title'}
            <div class="productlist-filter-headline">
                {$oBox->getTitle()}
            </div>
        {/block}
        {eval var=$oBox->getContent()}
    {/card}
{/block}
