{include file='tpl_inc/seite_header.tpl' cTitel=__('deleteLinkGroup')}
<div id="content">
    <div class="card">
        <div class="card-body">
            <form method="post" action="links.php">
                {$jtl_token}
                <input type="hidden" name="action" value="confirm-delete" />
                <input type="hidden" name="kLinkgruppe" value="{$linkGroup->getID()}" />

                <div class="alert alert-danger">
                    <p><strong>{__('danger')}</strong>: {__('dangerDeleteAllLinksInLinkGroup')}</p>
                    {if $affectedLinkNames|count > 0}
                        <p>{__('dangerDeleteLinksAlso')}:</p>
                        <ul class="list">
                            {foreach $affectedLinkNames as $link}
                                <li>{$link}</li>
                            {/foreach}
                        </ul>
                    {/if}
                    <p>{{__('sureDeleteLinkGroup')}|sprintf:{$linkGroup->getName()}}</p>
                </div>
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto mb-2">
                        <button type="submit" name="confirmation" value="1" value="{__('yes')}" class="btn btn-danger btn-block min-w-sm">
                            <i class="fal fa-check"></i> {__('yes')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" name="confirmation" value="0" value="{__('no')}" class="btn btn-outline-primary btn-block min-w-sm">
                            <i class="fa fa-close"></i> {__('no')}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
