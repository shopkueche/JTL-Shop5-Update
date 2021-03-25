<div id="opcSidebar">
    <header id="opcHeader">
        <div class="opc-dropdown" id="opcMenuBtnDropdown">
            <button type="button" id="opcMenuBtn" data-toggle="dropdown" class="opc-header-btn">
                <i class="fa fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu opc-dropdown-menu">
                <button type="button" class="opc-dropdown-item" onclick="opc.gui.importDraft()">
                    <i class="fa fas fa-upload fa-fw"></i> {__('Import')}
                </button>
                <button type="button" class="opc-dropdown-item" onclick="opc.gui.exportDraft()">
                    <i class="fa fas fa-download fa-fw"></i> {__('Export')}
                </button>
                <button type="button" class="opc-dropdown-item" id="btnHelp" onclick="opc.tutorial.start()" ">
                    <i class="fa fas fa-question-circle fa-fw"></i> {__('help')}
                </button>
            </div>
        </div>
        <h1 id="opc-sidebar-title">
            {__('editPage')}
        </h1>
        <button type="button" onclick="opc.gui.closeEditor()" class="opc-float-right opc-header-btn"
                data-toggle="tooltip" data-placement="bottom" title="{__('Close OnPage-Composer')}">
            <i class="fa fas fa-times"></i>
        </button>
    </header>

    <ul class="nav nav-tabs" id="opcTabs">
        <li class="nav-item">
            <a class="nav-link active" href="#portlets" data-toggle="tab">{__('Portlets')}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#blueprints" data-toggle="tab">{__('Blueprints')}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#revisions" data-toggle="tab">{__('Revisions')}</a>
        </li>
        <li class="nav-item" id="btnPagetree">
            <a class="nav-link" href="#pagetree" data-toggle="tab">{__('Page structure')}</a>
        </li>
    </ul>

    <div id="sidebarInnerPanel">
        <div class="tab-content">
            <div class="tab-pane show active" id="portlets">
                {foreach $opc->getPortletGroups() as $group}
                    {$groupId = $group->getName()|regex_replace:'/[^a-zA-Z0-9]/':'-'|lower}
                    <button class="portletGroupBtn" type="button"
                            data-toggle="collapse" data-target="#collapse-{$groupId}">
                        {$group->getName()} <i class="fas fa-chevron-up"></i>
                    </button>
                    <div class="collapse show" id="collapse-{$groupId}">
                        {foreach $group->getPortlets() as $i => $portlet}
                            <button type="button" class="portletButton" draggable="true"
                                    data-portlet-class="{$portlet->getClass()}"
                                    data-portlet-group="{$groupId}"
                                    data-portlet-css="{$portlet->getCssFile(true)}">
                                <span class="portletBtnInner">
                                    {$portlet->getButtonHtml()}
                                </span>
                            </button>
                        {/foreach}
                    </div>
                    {if !$group@last}
                        <hr>
                    {/if}
                {/foreach}
            </div>
            <div class="tab-pane" id="blueprints">
                <div id="blueprintList"></div>
                <button type="button" class="opc-btn-secondary opc-small-btn" onclick="opc.gui.importBlueprint()"
                        id="btnImportBlueprint">
                    <i class="fa fa-upload"></i> <span>{__('Import blueprint')}</span>
                </button>
            </div>
            <div class="tab-pane" id="revisions">
                <a class="revisionBtn" href="#" data-revision-id="-1" id="unsavedRevision">
                    <i>{__('Unsaved revision')}</i>
                </a>
                <a class="revisionBtn" href="#" data-revision-id="0">
                    <div>
                        <span id="currentDraftName"></span>
                        <span class="opc-status-draft">({__('Current revision')})</span>
                    </div>
                    <div id="currentLastModified"></div>
                </a>
                <div id="revisionList"></div>
            </div>
            <div class="tab-pane" id="pagetree">
                <div id="pageTreeView"></div>
            </div>
        </div>
    </div>

    <div id="sidebarFooter">
        <div id="savePublishPanel">
            <label for="footerDraftNameInput" id="footerDraftName" onclick="opc.gui.onBeginEditDraftName()">
                <span>
                    {if isset($page)}
                        {$page->getName()}
                    {else}
                        No Page
                    {/if}
                </span>
                <i class="fas fa-pencil-alt"></i>
            </label>
            <input type="text" id="footerDraftNameInput" onblur="opc.gui.onFinishEditDraftName()"
                   onkeydown="opc.gui.onDraftNameInputKeydown()" style="display:none">
            <div class="opc-draft-status" id="opcDraftStatus">
                {if isset($page)}
                    {include file="./draftstatus.tpl"}
                {else}
                    No Page
                {/if}
            </div>
            <div id="savePublishButtons">
                <button type="button" class="opc-btn-secondary opc-small-btn" onclick="opc.gui.savePage()">
                    {__('save')} <i class="fas fa-asterisk" id="unsavedState" style="display: none"></i>
                </button>
                <button type="button" class="opc-btn-primary opc-small-btn" onclick="opc.gui.publishDraft()"
                        id="btnPublishDraft">
                    {__('Publish')}
                </button>
            </div>
        </div>
        <div id="previewToolbar">
            <label class="toggle-switch">
                {__('preview')}
                <input type="checkbox" onchange="opc.gui.onBtnPreview()">
                <span class="toggle-slider"></span>
            </label>
            <ul id="displayWidths">
                <li>
                    <button onclick="opc.gui.setDisplayWidthXS()"><i class="fas fa-mobile-alt"></i></button>
                </li>
                <li>
                    <button onclick="opc.gui.setDisplayWidthSM()"><i class="fas fa-tablet-alt"></i></button>
                </li>
                <li>
                    <button onclick="opc.gui.setDisplayWidthMD()"><i class="fas fa-laptop"></i></button>
                </li>
                <li>
                    <button onclick="opc.gui.setDisplayWidthLG()"><i class="fas fa-desktop"></i></button>
                </li>
                <li>
                    <button onclick="opc.gui.setDisplayWidthXL()" class="active">
                        <i class="fas fa-expand"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div id="disableVeil" style="display: none"></div>
</div>

{*blueprint for blueprint entry*}
<div class="blueprintButton" style="display:none" id="blueprintBtnBlueprint" draggable="true" data-blueprint-id="42">
    <i class="blueprint-icon fas fa-puzzle-piece"></i>
    <div class="blueprint-btn-actions float-right">
        <button type="button" class="blueprintExport" data-blueprint-id="999" title="Vorlage exportieren">
            <i class="fas fa-download fa-fw"></i>
        </button>
        <button type="button" class="blueprintDelete" data-blueprint-id="999" title="Vorlage löschen">
            <i class="fas fa-trash fa-fw"></i>
        </button>
    </div>
    <span class="blueprintTitle mr-5">{__('templateTitle')}</span>
</div>
{*/blueprint*}

{*blueprint for revision entry*}
<a class="revisionBtn" href="#" data-revision-id="999"
   style="display:none" id="revisionBtnBlueprint"></a>
{*/blueprint*}