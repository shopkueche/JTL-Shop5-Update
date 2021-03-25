{foreach $children as $childCat}
    <tr class="tab_bg{$childCat@iteration % 2}{if $childCat->getLevel() > 1} hidden-soft{/if}" data-level="{$childCat->getLevel()}">
        <td class="check">
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" name="kNewsKategorie[]" data-name="{$childCat->getName()}" value="{$childCat->getID()}" id="newscat-{$childCat->getID()}" />
                <label class="custom-control-label" for="newscat-{$childCat->getID()}"></label>
            </div>
        </td>
        <td class="TD2{if $childCat->getLevel() === 1} hide-toggle-on{/if}" data-name="category">
            <i class="fa fa-caret-down nav-toggle{if $childCat->getChildren()->count() === 0} invisible{/if} cursor-pointer"></i>
            <label for="newscat-{$childCat->getID()}">
                {for $i=2 to $childCat->getLevel()}&bull;&nbsp;{/for}{$childCat->getName()}
            </label>
        </td>
        <td class="text-center">{$childCat->getSort()}</td>
        <td class="text-center">
            <i class="fal fa-{if $childCat->getIsActive()}check text-success{else}times text-danger{/if}"></i>
        </td>
        <td class="text-center">{$childCat->getDateLastModified()->format('d.m.Y H:i')}</td>
        <td class="text-center">
            <div class="btn-group">
                <a href="news.php?news=1&newskategorie_editieren=1&kNewsKategorie={$childCat->getID()}&tab=kategorien&token={$smarty.session.jtl_token}"
                   class="btn btn-link" title="{__('modify')}">
                    <i class="fal fa-edit"></i>
                </a>
            </div>
        </td>
    </tr>
    {include 'tpl_inc/newscategories_recursive.tpl' children=$childCat->getChildren() level=$childCat->getLevel()}
{/foreach}