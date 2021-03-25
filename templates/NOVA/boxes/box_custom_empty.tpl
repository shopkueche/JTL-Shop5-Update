{block name='boxes-box-custom-empty'}
    {card class="box box-custom box-normal" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-custom-empty-content'}
            {eval var=$oBox->getContent()}
        {/block}
    {/card}
{/block}
