{include file='tpl_inc/header.tpl'}

{if 'DASHBOARD_VIEW'|permission}
    <script type="text/javascript">

    function addWidget(kWidget) {
        ioCall(
            'addWidget', [kWidget], function () {
                window.location.href='index.php?kWidget=' + kWidget;
            }
        );
    }

    $(function() {
        ioCall('truncateJtllog', undefined, undefined, undefined, undefined, true);
    });
    </script>

    <div id="content">
        <div class="row p-2">
            <div class="col">
                <h1 class="content-header-headline">{__('dashboard')}</h1>
            </div>
            <div class="col-auto ml-auto">
                <div class="dropleft d-inline-block">
                    <button class="btn btn-link btn-lg p-0" type="button" id="helpcenter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="icon-hover">
                            <span class="fal fa-cog"></span>
                            <span class="fas fa-cog"></span>
                        </span>
                    </button>
                    <div id="available-widgets" class="dropdown-menu dropdown-menu-right min-w-lg" aria-labelledby="helpcenter">
                        {include file='tpl_inc/widget_selector.tpl' oAvailableWidget_arr=$oAvailableWidget_arr}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            {include file='tpl_inc/widget_container.tpl' eContainer='left'}
            {include file='tpl_inc/widget_container.tpl' eContainer='center'}
            {include file='tpl_inc/widget_container.tpl' eContainer='right'}
        </div>
    </div>
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('dashboard')}
    <div class="alert alert-success">
        <strong>{__('noMoreInfo')}</strong>
    </div>
{/if}

{include file='tpl_inc/footer.tpl'}
