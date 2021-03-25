<div class="modal fade" tabindex="-1" id="publishModal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{__('draftPublic')}</h5>
                <button type="button" class="opc-header-btn" data-toggle="tooltip" data-dismiss="modal"
                        data-placement="bottom">
                    <i class="fa fas fa-times"></i>
                </button>
            </div>
            <form onsubmit="opc.gui.publish()">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="draftName">{__('draftName')}</label>
                        <input type="text" class="form-control opc-control" id="draftName" name="draftName"
                               value="">
                    </div>
                    <div class="form-group">
                        <input type="radio" id="checkPublishNot" name="scheduleStrategy"
                               onchange="opc.gui.onChangePublishStrategy()">
                        <label for="checkPublishNot">
                            {__('publishNot')}
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="radio" id="checkPublishNow" name="scheduleStrategy"
                               onchange="opc.gui.onChangePublishStrategy()">
                        <label for="checkPublishNow">
                            {__('publishImmediately')}
                        </label>
                    </div>
                    <div style="display:flex;">
                        <div class="form-group" style="width:50%">
                            <input type="radio" id="checkPublishSchedule" name="scheduleStrategy"
                                   onchange="opc.gui.onChangePublishStrategy()">
                            <label for="checkPublishSchedule">
                                {__('selectDate')}
                            </label>
                        </div>
                        <div class="form-group" style="width:50%">
                            <input type="checkbox" id="checkPublishInfinite"
                                   onchange="opc.gui.onChangePublishInfinite()">
                            <label for="checkPublishInfinite">
                                {__('indefinitePeriodOfTime')}
                            </label>
                        </div>
                    </div>
                    <div style="display:flex;">
                        <div class="form-group" style="width:50%;padding-right:16px">
                            <label for="publishFrom">{__('publicFrom')}</label>
                            <input type="text" class="form-control opc-control datetimepicker-input" id="publishFrom"
                                   name="publishFrom" data-toggle="datetimepicker" data-target="#publishFrom">
                        </div>
                        <div class="form-group" style="width:50%">
                            <label for="publishTo">{__('publicTill')}</label>
                            <input type="text" class="form-control opc-control datetimepicker-input" id="publishTo"
                                   name="publishTo" data-toggle="datetimepicker" data-target="#publishTo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="opc-btn-secondary opc-small-btn" data-dismiss="modal"
                            id="btnCancelPublish">
                        {__('cancel')}
                    </button>
                    <button type="submit" class="opc-btn-primary opc-small-btn" id="btnApplyPublish">
                        {__('apply')}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>