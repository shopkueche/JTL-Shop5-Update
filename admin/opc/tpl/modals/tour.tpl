<div id="tourModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{__('help')}</h5>
                <button type="button" class="opc-header-btn" data-toggle="tooltip" data-dismiss="modal"
                        data-placement="bottom">
                    <i class="fa fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>{__('noteInfoInGuide')}</p>
                <div class="card" onclick="opc.tutorial.startTour(0)">
                    <div class="card-header">{__('generalIntroduction')}</div>
                    <div class="card-body">{__('getToKnowComposer')}</div>
                </div>
                <div class="card" onclick="opc.tutorial.startTour(1)">
                    <div class="card-header">{__('animation')}</div>
                    <div class="card-body">{__('noteMovementOnPage')}</div>
                </div>
                <div class="card" onclick="opc.tutorial.startTour(2)">
                    <div class="card-header">{__('templates')}</div>
                    <div class="card-body">{__('noteSaveAsTemplate')}</div>
                </div>
            </div>
        </div>
    </div>
</div>