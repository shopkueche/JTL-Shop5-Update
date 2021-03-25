<div id="unbound-licenses">
    {if isset($bindErrorMessage)}
        <div class="alert alert-danger">
            {__($bindErrorMessage)}
        </div>
    {/if}
    <div class="card">
        <div class="card-header">
            {__('Unbound licenses')}
            <hr class="mb-n3">
        </div>
        <div class="card-body">
        {if $licenses->getUnbound()->count() > 0}
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>{__('ID')}</th>
                        <th>{__('Name')}</th>
                        <th>{__('Type')}</th>
                        <th>{__('Developer')}</th>
                        <th>{__('Action')}</th>
                    </tr>
                    </thead>
                    {foreach $licenses->getUnbound() as $license}
                        <tr>
                            <td>{$license->getID()}</td>
                            <td>
                                {$license->getName()}
                                {$textLinks = []}
                                {foreach $license->getLinks() as $link}
                                    {if $link->getRel() === 'itemDetails' || $link->getRel() === 'documentation'}
                                        {$btn = ['rel' => $link->getRel(), 'href' => $link->getHref()]}
                                        {$textLinks[] = $btn}
                                    {/if}
                                {/foreach}
                                {if $textLinks|count > 0}
                                    <p class="links small mb-0">
                                        {foreach $textLinks as $link}
                                            <a class="text-link" rel="noopener" href="{$link.href}" title="{__($link.rel)}">
                                                {__($link.rel)}
                                            </a>{if !$link@last} | {/if}
                                        {/foreach}
                                    </p>
                                {/if}
                            </td>
                            <td>{__($license->getLicense()->getType())}</td>
                            <td><a href="{$license->getVendor()->getHref()}" rel="noopener">{$license->getVendor()->getName()}</a></td>
                            <td>
                                {foreach $license->getLinks() as $link}
                                    {if $link->getRel() === 'setBinding'}
                                        {form class='set-binding-form' style='display:inline-block'}
                                            <input type="hidden" name="action" value="setbinding">
                                            <input type="hidden" name="url" value="{$link->getHref()}">
                                            <input type="hidden" name="method" value="{$link->getMethod()|default:'POST'}">
                                            <button type="submit" class="btn btn-sm btn-default set-binding"
                                                    data-link="{$link->getHref()}" href="#" title="{__($link->getRel())}">
                                                <i class="fa fa-link"></i> {__($link->getRel())}
                                            </button>
                                        {/form}
                                    {/if}
                                {/foreach}
                            </td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        {else}
            <p class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noData')}</p>
        {/if}
        </div>
    </div>
</div>
