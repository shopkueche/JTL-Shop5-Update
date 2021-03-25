{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('favorites') cBeschreibung=__('manageFavorites')}

<script type="text/javascript">
    function addItem() {
        var last = $("#favs tbody tr:last-child");
        var title = last.find('input[name="title[]"]').val();
        var url = last.find('input[name="url[]"]').val();
        if (title.length > 0 || url.length > 0) {
            var next = last.clone();
            next.find('input').val('');
            $("#favs tbody").append(next);
        }
    }

    $(function() {
        $("#favs tbody").sortable({
            placeholder: "ui-state-highlight"
        });

        $("#favs tbody").disableSelection();

        $("body").on('click', "#favs tbody button.btn-remove", function() {
            var cnt = $("#favs tbody tr").length;
            if (cnt > 1) {
                $(this)
                    .closest('tr')
                    .remove();
            }
            else {
                $(this)
                    .closest('tr')
                    .find('input')
                    .val('');
            }
        });

        $("body").on('change keyup', "#favs tbody input", function() {
            addItem();
        });

        addItem();
    });
</script>

{function fav_item title='' url=''}
    <tr class="text-vcenter">
        <td class="text-left">
            <input class="form-control" type="text" name="title[]" value="{$title}">
        </td>
        <td class="text-left">
            <input class="form-control" type="text" name="url[]" value="{$url}">
        </td>
        <th class="text-muted text-center" scope="row">
            <i class="fal fa-arrows-v" aria-hidden="true"></i>
        </th>
        <td class="text-center">
            <button type="button" class="btn btn-link btn-remove" data-toggle="tooltip" title="{__('delete')}">
                <span class="icon-hover">
                    <span class="fal fa-trash-alt"></span>
                    <span class="fas fa-trash-alt"></span>
                </span>
            </button>
        </td>
    </tr>
{/function}

<div id="content">
    <form method="post">
        {$jtl_token}
        <div class="card">
            <div class="table-responsive card-body">
                <table class="list table table-hover" id="favs">
                    <thead>
                    <tr>
                        <th class="text-left">{__('title')}</th>
                        <th class="text-left">{__('link')}</th>
                        <th width="30"></th>
                        <th width="50"></th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach $favorites as $favorite}
                            {fav_item title=$favorite->cTitel url=$favorite->cUrl}
                        {foreachelse}
                            {fav_item title='' url=''}
                        {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="submit" name="action" value="save" class="btn btn-primary btn-block">
                        {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
