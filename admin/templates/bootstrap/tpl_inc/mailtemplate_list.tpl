{if $mailTemplates|count > 0}
    <div class="card">
        <div class="card-header">
            <div class="subheading1">{$heading}</div>
            <hr class="mb-n3">
        </div>
        <div class="card-body table-responsive">
            <table class="list table table-sm table-hover">
                <thead>
                <tr>
                    <th class="text-left">{__('template')}</th>
                    <th class="text-center">{__('type')}</th>
                    <th class="text-center">{__('active')}</th>
                    <th class="text-center">{__('options')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $mailTemplates as $template}
                    <tr>
                        <td>{__('name_'|cat:$template->getModuleID())}</td>
                        <td class="text-center">{$template->getType()}</td>
                        <td class="text-center" id="tplState_{$template->getID()}">
                            {include file='snippets/mailtemplate_state.tpl' template=$template}
                        </td>
                        <td class="text-center">
                            <form method="post" action="emailvorlagen.php">
                                {if $template->getPluginID() > 0}
                                    <input type="hidden" name="kPlugin" value="{$template->getPluginID()}" />
                                {/if}
                                {$jtl_token}
                                <div class="btn-group">
                                    <button type="button" data-id="{$template->getID()}" class="btn btn-link px-2 btn-syntaxcheck" title="{__('Check syntax')}" data-toggle="tooltip" data-placement="top" >
                                        <span class="icon-hover">
                                            <span class="fal fa-check"></span>
                                            <span class="fas fa-check"></span>
                                        </span>
                                    </button>
                                    <button type="submit" name="resetConfirm" value="{$template->getID()}" class="btn btn-link px-2 reset" title="{__('reset')}" data-toggle="tooltip" data-placement="top" >
                                        <span class="icon-hover">
                                            <span class="fal fa-refresh"></span>
                                            <span class="fas fa-refresh"></span>
                                        </span>
                                    </button>
                                    <button type="submit" name="preview" value="{$template->getID()}" title="{__('testmail')}" class="btn btn-link px-2 mail" data-toggle="tooltip" data-placement="top" >
                                        <span class="icon-hover">
                                            <span class="fal fa-envelope"></span>
                                            <span class="fas fa-envelope"></span>
                                        </span>
                                    </button>
                                    <button type="submit" name="kEmailvorlage" value="{$template->getID()}" class="btn btn-link px-2" title="{__('modify')}" data-toggle="tooltip" data-placement="top" >
                                        <span class="icon-hover">
                                            <span class="fal fa-edit"></span>
                                            <span class="fas fa-edit"></span>
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}
