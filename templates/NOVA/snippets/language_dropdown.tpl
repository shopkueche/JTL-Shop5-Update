{block name='snippets-language-dropdown'}
    {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
        {navitemdropdown
        class="language-dropdown {$dropdownClass|default:''}"
        right=true
        text="
            {foreach $smarty.session.Sprachen as $language}
                {if $language->kSprache == $smarty.session.kSprache}
                    {block name='snippets-language-dropdown-text'}
                        {$language->iso639|upper}
                    {/block}
                {/if}
            {/foreach}"
        }
            {foreach $smarty.session.Sprachen as $language}
                {block name='snippets-language-dropdown-item'}
                    {dropdownitem href="{$language->cURL}"
                        class="link-lang"
                        data=["iso"=>$language->cISO]
                        rel="nofollow"
                        active=($language->kSprache == $smarty.session.kSprache)}
                        {$language->iso639|upper}
                    {/dropdownitem}
                {/block}
            {/foreach}
        {/navitemdropdown}
    {/if}
{/block}
