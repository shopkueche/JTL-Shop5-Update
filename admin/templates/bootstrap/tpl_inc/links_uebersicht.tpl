<script type="text/javascript">
    function confirmDelete() {
        return confirm('{__('sureDeleteLink')|replace:"\n":' '}');
    }
    $(document).ready(function () {
        $('.duplicate-special-link').closest('.link-group-wrapper').find('.duplicate-special-page-warning')
            .removeClass('d-none');
    });
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('links') cBeschreibung=__('linksDesc') cDokuURL=__('linksUrl')}
<div id="content">
    <form action="links.php" method="post">
        {$jtl_token}
        <div class="row">
            <div class="col-sm-6 col-xl-auto">
                <button class="btn btn-primary add btn-block mb-4" name="action" value="create-linkgroup">
                    <i class="fa fa-share"></i> {__('newLinkGroup')}
                </button>
            </div>
        </div>
    </form>
    <div class="accordion" id="accordion2" role="tablist" aria-multiselectable="true">
        {foreach $linkgruppen as $linkgruppe}
            {if $linkgruppe->getID() < 0 && $linkgruppe->getLinks()->count() === 0}
                {continue}
            {/if}
            {$lgName = 'linkgroup-'|cat:$linkgruppe->getID()}
            {$missingTranslations = $linkAdmin->getMissingLinkGroupTranslations($linkgruppe->getID())}
            <div class="card panel-{if $linkgruppe->getID() > 0}default{else}danger{/if} link-group-wrapper">
                <div class="card-header row accordion-heading">
                    <div class="subheading1 col-md-6" id="heading-{$lgName}">
                        <span class="pull-left">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                               href="#collapse{$lgName}">
                                <span class="accordion-toggle-icon"><i class="fal fa-plus"></i></span>
                                {if $linkgruppe->getID() > 0}
                                    {$linkgruppe->getName()} ({__('linkGroupTemplatename')}: {$linkgruppe->getTemplate()})
                                {else}
                                    {__('linksWithoutLinkGroup')}
                                {/if}
                            </a>
                            {if $missingTranslations|count > 0}
                                <i class="fal fa-exclamation-triangle text-warning"
                                      data-toggle="tooltip"
                                      data-placement="top"
                                      title="{__('missingTranslations')}: {$missingTranslations|count}"></i>
                            {/if}
                            <i title="{__('hasAtLeastOneDuplicateSpecialLink')}"
                               class="d-none duplicate-special-page-warning fal fa-exclamation-triangle text-danger"
                               data-toggle="tooltip"
                               data-placement="top"></i>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <form method="post" action="links.php">
                            {$jtl_token}
                            {if $linkgruppe->getID() > 0}
                                <input type="hidden" name="kLinkgruppe" value="{$linkgruppe->getID()}">
                            {/if}
                            <div class="btn-group float-right">
                                {if $linkgruppe->getID() > 0}
                                    <button name="action" value="delete-linkgroup" class="btn btn-link px-2" title="{__('linkGroup')} {__('delete')}" data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-trash-alt"></span>
                                            <span class="fas fa-trash-alt"></span>
                                        </span>
                                    </button>
                                    <button name="action" value="add-link-to-linkgroup" class="btn btn-link px-2 add" title="{__('addLink')}" data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-plus"></span>
                                            <span class="fas fa-plus"></span>
                                        </span>
                                    </button>
                                    <button name="action" value="edit-linkgroup" class="btn btn-link px-2" title="{__('modify')}" data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-edit"></span>
                                            <span class="fas fa-edit"></span>
                                        </span>
                                    </button>
                                {/if}
                            </div>
                        </form>
                    </div>
                </div>
                <div id="collapse{$lgName}" class="card-body collapse" role="tabpanel" aria-labelledby="heading-{$lgName}">
                    {if $linkgruppe->getLinks()->count() > 0}
                        <div class="table-responsive">
                            <table class="table">
                                {include file='tpl_inc/links_uebersicht_item.tpl' list=$linkgruppe->getLinks() id=$linkgruppe->getID()}
                            </table>
                        </div>
                    {else}
                        <p class="alert alert-info" style="margin:10px;"><i class="fal fa-info-circle"></i> {__('noData')}</p>
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>{* /accordion *}
    <form action="links.php" method="post">
        {$jtl_token}
        <div class="row">
            <div class="col-sm-6 col-xl-auto mb-4">
                <button class="btn btn-primary add btn-block" name="action" value="create-linkgroup">
                    <i class="fa fa-share"></i> {__('newLinkGroup')}
                </button>
            </div>
        </div>
    </form>
</div>
