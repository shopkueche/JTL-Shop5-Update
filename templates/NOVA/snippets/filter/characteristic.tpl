{block name='snippets-filter-characteristic'}
    {$is_dropdown = ($Merkmal->cTyp === 'SELECTBOX')}
    {$limit = $Einstellungen.template.productlist.filter_max_options}
    {$collapseInit = false}
    {if $Merkmal->getData('cTyp') === 'BILD'}
        <ul class="nav nav-filter-has-image">
    {/if}
    {foreach $Merkmal->getOptions() as $attributeValue}
        {$attributeImageURL = null}
        {if ($Merkmal->getData('cTyp') === 'BILD' || $Merkmal->getData('cTyp') === 'BILD-TEXT')}
            {$attributeImageURL = $attributeValue->getImage(\JTL\Media\Image::SIZE_XS)}
            {if $attributeImageURL|strpos:$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN !== false
                || $attributeImageURL|strpos:$smarty.const.BILD_KEIN_MERKMALWERTBILD_VORHANDEN !== false}
                {$attributeImageURL = null}
            {/if}
        {/if}
        {if $is_dropdown}
            {block name='snippets-filter-characteristics-dropdown'}
                {dropdownitem
                    class="{if $attributeValue->isActive()}active{/if} filter-item"
                    href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
                    title="{if $Merkmal->getData('cTyp') === 'BILD'}{$attributeValue->getValue()|escape:'html'}{/if}"
                }
                    <div class="box-link-wrapper">
                        <i class="far fa-{if $attributeValue->isActive()}check-{/if}square snippets-filter-item-icon-right"></i>
                        {if !empty($attributeImageURL)}
                            {image lazy=true webp=true
                                src=$attributeImageURL
                                alt=$attributeValue->getValue()|escape:'html'
                                class="vmiddle"
                            }
                        {/if}
                        <span class="word-break">{$attributeValue->getValue()|escape:'html'}</span>
                        {badge variant="outline-secondary"}{$attributeValue->getCount()}{/badge}
                    </div>
                {/dropdownitem}
            {/block}
        {else}
            {if $limit != -1 && $attributeValue@iteration > $limit && !$collapseInit}
                {block name='snippets-filter-characteristics-more-top'}
                    <div class="collapse {if $Merkmal->isActive()} show{/if}" id="box-collps-filter-attribute-{$Merkmal->getValue()}" aria-expanded="false" role="button">
                        <ul class="nav {if $Merkmal->getData('cTyp') !== 'BILD'}flex-column{/if}">
                    {$collapseInit = true}
                {/block}
            {/if}
            {block name='snippets-filter-characteristics-nav'}
                {if {$Merkmal->getData('cTyp')} === 'TEXT'}
                    {block name='snippets-filter-characteristics-nav-text'}
                        {link
                            class="{if $attributeValue->isActive()}active{/if} filter-item"
                            href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
                            title="{$attributeValue->getValue()|escape:'html'}"
                        }
                            <div class="box-link-wrapper">
                                <i class="far fa-{if $attributeValue->isActive()}check-{/if}square snippets-filter-item-icon-right"></i>
                                {if !empty($attributeImageURL)}
                                    {image lazy=true webp=true
                                        src=$attributeImageURL
                                        alt=$attributeValue->getValue()|escape:'html'
                                        class="vmiddle"
                                    }
                                {/if}
                                <span class="word-break">{$attributeValue->getValue()|escape:'html'}</span>
                                {badge variant="outline-secondary"}{$attributeValue->getCount()}{/badge}
                            </div>
                        {/link}
                    {/block}
                {elseif $Merkmal->getData('cTyp') === 'BILD' && $attributeImageURL !== null}
                    {block name='snippets-filter-characteristics-nav-image'}
                        {link href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
                            title="{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}"
                            data=["toggle"=>"tooltip", "placement"=>"top", "boundary"=>"window"]
                            class="{if $attributeValue->isActive()}active{/if} filter-item"
                        }
                            {image lazy=true  webp=true
                                src=$attributeImageURL
                                alt=$attributeValue->getValue()|escape:'html'
                                title="{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}"
                                class="vmiddle filter-img"
                            }
                        {/link}
                    {/block}
                {else}
                    {block name='snippets-filter-characteristics-nav-else'}
                        {link href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
                            title="{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}"
                            class="{if $attributeValue->isActive()}active{/if} filter-item"
                        }
                            <div class="box-link-wrapper">
                                {if !empty($attributeImageURL)}
                                    {image lazy=true webp=true
                                        src=$attributeImageURL
                                        alt=$attributeValue->getValue()|escape:'html'
                                        title="{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}"
                                        class="vmiddle filter-img"
                                    }
                                {/if}
                                <span class="word-break">
                                    {$attributeValue->getValue()|escape:'html'}
                                </span>
                                {badge variant="outline-secondary"}{$attributeValue->getCount()}{/badge}
                            </div>
                        {/link}
                    {/block}
                {/if}
            {/block}
        {/if}
    {/foreach}
    {if !$is_dropdown && $limit != -1 && $Merkmal->getOptions()|count > $limit}
        {block name='snippets-filter-characteristics-more-bottom'}
                </ul>
            </div>
            <div class="snippets-filter-show-all">
                {button variant="link"
                    role="button"
                    data=["toggle"=> "collapse", "target"=>"#box-collps-filter-attribute-{$Merkmal->getValue()}"]}
                    {lang key='showAll'}
                {/button}
            </div>
        {/block}
    {/if}
    {if $Merkmal->getData('cTyp') === 'BILD'}
        </ul>
    {/if}
{/block}
