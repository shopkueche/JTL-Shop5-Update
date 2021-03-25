{include file='tpl_inc/seite_header.tpl' cTitel=__('pageTitle') cBeschreibung=__('pageDesc') cDokuURL=__('docURL')}
{$select = $select|default:true}
{$edit = $edit|default:true}
{$delete = $delete|default:false}
{$save = $save|default:false}
{$enable = $enable|default:false}
{$disable = $disable|default:false}
{$action = $action|default:($shopURL|cat:$smarty.server.PHP_SELF)}
{$search = $search|default:false}
{$searchQuery = $searchQuery|default:null}
{$pagination = $pagination|default:null}
{$method = $method|default:'post'}

<div id="content">
    <div class="tabs">
        <nav class="tavs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $tab === 'overview'} active{/if}" data-toggle="tab" role="tab" href="#overview">
                        {__('modelHeader')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $tab === 'settings'} active{/if}" data-toggle="tab" role="tab" href="#config">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="overview" class="tab-pane fade{if $tab === 'overview'} active show{/if}">
                {if $items->count() > 0}
                    {if $search === true}
                        <div class="search-toolbar mb-3">
                            <form name="datamodel" method="post" action="{$action}">
                                {$jtl_token}
                                <input type="hidden" name="Suche" value="1" />
                                <div class="form-row">
                                    <label class="col-sm-auto col-form-label" for="modelsearch">{__('search')}:</label>
                                    <div class="col-sm-auto mb-2">
                                        <input class="form-control" name="cSuche" type="text" value="{if $searchQuery !== null}{$searchQuery}{/if}" id="modelsearch" />
                                    </div>
                                    <span class="col-sm-auto">
                                        <button name="submitSuche" type="submit" class="btn btn-primary btn-block"><i class="fal fa-search"></i></button>
                                    </span>
                                </div>
                            </form>
                        </div>
                    {/if}
                    {if $searchQuery !== null}
                        {$params = ['cSuche'=>$searchQuery]}
                    {else}
                        {$params = null}
                    {/if}
                    {if $pagination !== null}
                        {include file='tpl_inc/pagination.tpl' pagination=$pagination cParam_arr=$params}
                    {/if}
                    <form name="modelform" id="modelform" method="{$method}" action="{$action}">
                        {$jtl_token}
                        <input type="hidden" name="id" id="modelid" />
                        {if $search !== null}
                            <input type="hidden" name="cSuche" value="{$search}" />
                        {/if}
                        {$first = $items->first()}
                        <div class="table-responsive">
                            <table class="table table-striped table-align-top">
                                <thead>
                                <tr>
                                    {if $select === true}
                                        <th class="check">&nbsp;</th>
                                    {/if}
                                    {foreach $first->getAttributes() as $attr}
                                        {$type = $attr->getDataType()}
                                        {if $attr->getInputConfig()->isHidden() === false && (strpos($type, "\\") === false || !class_exists($type))}
                                            <th>{__({$attr->getName()})}</th>
                                        {/if}
                                    {/foreach}
                                    {if $edit === true}
                                        <th class="text-center">&nbsp;</th>
                                    {/if}
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $items as $item}
                                    <tr>
                                        {if $select === true}
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="mid[{$item@index}]" type="checkbox" value="{$item->getId()}" id="mid-{$item->getId()}" />
                                                    <label class="custom-control-label" for="mid-{$item->getId()}"></label>
                                                </div>
                                            </td>
                                        {/if}
                                        {foreach $item->getAttributes() as $attr}
                                            {$type = $attr->getDataType()}
                                            {if $attr->getInputConfig()->isHidden() === false && (strpos($type, "\\") === false || !class_exists($type))}
                                                <td>{$item->getAttribValue($attr->getName())}</td>
                                            {/if}
                                        {/foreach}
                                        {if $edit === true}
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{$action}?action=detail&id={$item->getId()}"
                                                       class="btn-prg btn btn-link px-2"
                                                       title="{__('modify')}"
                                                       data-toggle="tooltip">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-edit"></span>
                                                            <span class="fas fa-edit"></span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </td>
                                        {/if}
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                        <div class="save-wrapper">
                            <div class="row">
                                {if $select === true}
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                {/if}
                                {if $delete === true}
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="model-delete" type="submit" value="1" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('delete')}
                                        </button>
                                    </div>
                                {/if}
                                {if $save === true}
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="model-save" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                            <i class="fal fa-save"></i> {__('save')}
                                        </button>
                                    </div>
                                {/if}
                                {if $disable === true}
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="model-disable" type="submit" value="{__('disable')}" class="btn btn-warning btn-block">
                                            <i class="fa fa-close"></i> {__('disable')}
                                        </button>
                                    </div>
                                {/if}
                                {if $enable === true}
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="model-enable" type="submit" value="{__('enable')}" class="btn btn-primary btn-block">
                                            <i class="fa fa-check"></i> {__('enable')}
                                        </button>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="config" class="tab-pane fade{if $tab === 'settings'} active show{/if}">
                {include file='tpl_inc/config_section.tpl'
                config=$settings
                name='einstellen'
                a='saveSettings'
                action='consent.php'
                buttonCaption=__('saveWithIcon')
                tab='einstellungen'
                title=__('settings')}
            </div>
        </div>
    </div>
</div>
