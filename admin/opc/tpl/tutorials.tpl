<div id="tutorials">
    <div id="tutBackdrop"></div>
    <div id="tutBackdrop2"></div>
    <div id="tutBackdrop3"></div>
    <div id="tutBox">
        <div id="tutboxHeader" class="modal-header">
            <h5 id="tutboxTitle">Hello</h5>
            <button type="button" class="opc-header-btn" onclick="opc.tutorial.stopTutorial()">
                <i class="fa fas fa-times"></i>
            </button>
        </div>
        <div id="tutboxContent">Text</div>
        <div id="tutboxFooter">
            <button type="button" id="tutboxNext" class="opc-btn-primary opc-mini-btn opc-float-right"
                    onclick="opc.tutorial.goNextStep()">
                <span id="tutboxNextLabel">
                    {__('Next')}
                    <i class="fas fa-chevron-right"></i>
                </span>
                <span id="tutboxDoneLabel" style="display: none">
                    <i class="fas fa-check"></i>
                    {__('Done')}
                </span>
            </button>
            <button type="button" id="tutboxPrev" class="opc-btn-secondary opc-mini-btn opc-float-right"
                    onclick="opc.tutorial.goPrevStep()">
                <i class="fas fa-chevron-left"></i>
                {__('Back')}
            </button>
        </div>
    </div>
</div>