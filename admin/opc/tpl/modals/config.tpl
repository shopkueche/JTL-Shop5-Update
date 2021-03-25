<div id="configModal" class="modal fade" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="configModalTitle">
                    {__('editPortletPrefix')}
                    <span id="configPortletName"></span>
                    {__('editPortletPostfix')}
                </h5>
                <button type="button" class="opc-header-btn" data-toggle="tooltip" data-dismiss="modal"
                        data-placement="bottom">
                    <i class="fa fas fa-times"></i>
                </button>
            </div>
            <form id="configForm" onsubmit="opc.gui.saveConfig()">
                <div class="modal-body" id="configModalBody"></div>
                <div class="modal-footer" id="stdConfigButtons">
                    <button type="button" class="opc-btn-secondary opc-small-btn" data-dismiss="modal">
                        {__('cancel')}
                    </button>
                    <button type="submit" class="opc-btn-primary opc-small-btn" id="configSave">
                        {__('Save')}
                    </button>
                </div>
                <div class="modal-footer" id="missingConfigButtons">
                    <button type="submit" class="opc-btn-primary opc-small-btn"  data-dismiss="modal">
                        {__('OK')}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>