<div id="blueprintModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{__('Save this Portlet as a blueprint')}</h5>
            </div>
            <form onsubmit="opc.gui.createBlueprint()">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="blueprintName">{__('Blueprint name')}</label>
                        <input type="text" class="form-control" id="blueprintName" name="blueprintName"
                               value="Neue Vorlage">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="opc-btn-secondary opc-small-btn" data-dismiss="modal">
                        {__('cancel')}
                    </button>
                    <button type="submit" class="opc-btn-primary opc-small-btn" id="btnBlueprintSave">
                        {__('Save')}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>